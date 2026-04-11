#!/usr/bin/env python3
"""
ClearPay Python analytics worker.

This implementation intentionally uses the Python standard library only so it can
run against a plain local Python installation without extra package setup.
"""

from __future__ import annotations

import argparse
import csv
import json
from collections import Counter, defaultdict
from datetime import datetime, timedelta
from pathlib import Path
from statistics import mean
from typing import Any


VALID_PAYMENT_STATUSES = {"fully paid", "partial"}


def parse_datetime(value: Any) -> datetime | None:
    # Accept the timestamp formats that can come from PHP/DB rows and convert
    # them into one consistent Python datetime object for later calculations.
    if not value:
        return None
    text = str(value).strip()
    if not text:
        return None

    for candidate in (
        text,
        text.replace(" ", "T"),
        text.replace("Z", "+00:00"),
    ):
        try:
            return datetime.fromisoformat(candidate)
        except ValueError:
            continue

    for fmt in ("%Y-%m-%d %H:%M:%S", "%Y-%m-%d"):
        try:
            return datetime.strptime(text, fmt)
        except ValueError:
            continue

    return None


def as_float(value: Any) -> float:
    if value in (None, ""):
        return 0.0
    try:
        return float(value)
    except (TypeError, ValueError):
        return 0.0


def as_int(value: Any) -> int:
    if value in (None, ""):
        return 0
    try:
        return int(value)
    except (TypeError, ValueError):
        return 0


def iso(dt: datetime | None) -> str | None:
    return dt.isoformat() if dt else None


def quantile(values: list[float], fraction: float) -> float:
    # Lightweight percentile helper used for IQR-based outlier detection.
    if not values:
        return 0.0
    ordered = sorted(values)
    if len(ordered) == 1:
        return ordered[0]
    pos = (len(ordered) - 1) * fraction
    lower = int(pos)
    upper = min(lower + 1, len(ordered) - 1)
    weight = pos - lower
    return ordered[lower] * (1 - weight) + ordered[upper] * weight


def normalize_payload(payload: dict[str, Any]) -> tuple[list[dict[str, Any]], list[dict[str, Any]]]:
    # The PHP service passes raw database rows as JSON. Normalize the fields here
    # once so the rest of the analytics pipeline can treat amounts, ids, and
    # dates as typed values instead of repeatedly coercing them.
    payments: list[dict[str, Any]] = []
    for row in payload.get("payments", []):
        normalized = dict(row)
        normalized["amount_paid"] = as_float(row.get("amount_paid"))
        normalized["contribution_amount"] = as_float(row.get("contribution_amount"))
        normalized["cost_price"] = as_float(row.get("cost_price"))
        normalized["payment_sequence"] = as_int(row.get("payment_sequence"))
        normalized["payer_db_id"] = as_int(row.get("payer_db_id"))
        normalized["contribution_id"] = as_int(row.get("contribution_id"))
        normalized["created_at_dt"] = parse_datetime(row.get("created_at"))
        normalized["payment_date_dt"] = parse_datetime(row.get("payment_date")) or normalized["created_at_dt"]
        normalized["payment_day"] = normalized["payment_date_dt"].strftime("%Y-%m-%d") if normalized["payment_date_dt"] else ""
        normalized["receipt_number"] = str(row.get("receipt_number") or "").strip()
        payments.append(normalized)

    contributions: list[dict[str, Any]] = []
    for row in payload.get("contributions", []):
        normalized = dict(row)
        normalized["id"] = as_int(row.get("id"))
        normalized["amount"] = as_float(row.get("amount"))
        normalized["cost_price"] = as_float(row.get("cost_price"))
        normalized["contribution_type"] = str(row.get("contribution_type") or "contribution").lower()
        normalized["created_at_dt"] = parse_datetime(row.get("created_at"))
        contributions.append(normalized)

    return payments, contributions


def profit_summary(contributions: list[dict[str, Any]]) -> dict[str, float]:
    # Profit analytics are contribution-based, not payment-row-based.
    active = [row for row in contributions if str(row.get("status") or "").lower() == "active"]
    if not active:
        return {"total_profit": 0.0, "avg_profit_margin": 0.0}

    profits = []
    margins = []
    for row in active:
        profit = row["amount"] - row["cost_price"]
        profits.append(profit)
        margins.append((profit / row["amount"]) * 100 if row["amount"] else 0.0)

    return {
        "total_profit": round(sum(profits), 2),
        "avg_profit_margin": round(mean(margins), 2) if margins else 0.0,
    }


def top_profitable(contributions: list[dict[str, Any]], limit: int = 10) -> list[dict[str, Any]]:
    # Rank active contributions by absolute profit so the admin UI can show the
    # highest-value items first.
    active = [row for row in contributions if str(row.get("status") or "").lower() == "active"]
    ranked = []
    for row in active:
        profit = row["amount"] - row["cost_price"]
        margin = (profit / row["amount"]) * 100 if row["amount"] else 0.0
        ranked.append(
            {
                "id": row["id"],
                "title": row.get("title"),
                "contribution_type": row.get("contribution_type") or "contribution",
                "amount": row["amount"],
                "category": row.get("category") or "General",
                "status": row.get("status"),
                "cost_price": row["cost_price"],
                "profit_amount": round(profit, 2),
                "profit_margin": round(margin, 2),
            }
        )
    ranked.sort(key=lambda item: item["profit_amount"], reverse=True)
    return ranked[:limit]


def category_breakdown(contributions: list[dict[str, Any]]) -> list[dict[str, Any]]:
    # Summarize contribution performance by category for dashboard cards/tables.
    categories: dict[str, dict[str, float | int | str]] = {}
    for row in contributions:
        if str(row.get("status") or "").lower() != "active":
            continue
        category = row.get("category") or "General"
        bucket = categories.setdefault(category, {"category": category, "count": 0, "total_amount": 0.0, "total_profit": 0.0})
        bucket["count"] += 1
        bucket["total_amount"] += row["amount"]
        bucket["total_profit"] += row["amount"] - row["cost_price"]
    return sorted(categories.values(), key=lambda item: item["total_amount"], reverse=True)


def type_breakdown(contributions: list[dict[str, Any]]) -> list[dict[str, Any]]:
    types: dict[str, dict[str, float | int | str]] = {}
    for row in contributions:
        item_type = str(row.get("contribution_type") or "contribution").lower()
        bucket = types.setdefault(
            item_type,
            {"contribution_type": item_type, "count": 0, "total_amount": 0.0, "total_profit": 0.0},
        )
        bucket["count"] += 1
        bucket["total_amount"] += row["amount"]
        bucket["total_profit"] += row["amount"] - row["cost_price"]
    return sorted(types.values(), key=lambda item: item["count"], reverse=True)


def detect_duplicates(payments: list[dict[str, Any]]) -> list[dict[str, Any]]:
    # Duplicate detection uses two signals:
    # 1. the exact same receipt number appearing more than once
    # 2. the same payer/contribution/amount/method/day pattern appearing more than once
    #
    # Each payment id only appears once in the final output even if it matches
    # both rules.
    receipt_groups: dict[str, list[dict[str, Any]]] = defaultdict(list)
    composite_groups: dict[tuple[Any, ...], list[dict[str, Any]]] = defaultdict(list)

    for row in payments:
        if row["receipt_number"]:
            receipt_groups[row["receipt_number"]].append(row)
        composite_key = (
            row["payer_db_id"],
            row["contribution_id"],
            row["amount_paid"],
            str(row.get("payment_method") or "").lower(),
            row["payment_day"],
        )
        composite_groups[composite_key].append(row)

    duplicate_map: dict[int, dict[str, Any]] = {}

    for group in receipt_groups.values():
        if len(group) < 2:
            continue
        for row in group:
            duplicate_map[row["id"]] = {
                "id": row["id"],
                "payer_name": row.get("payer_name"),
                "payer_id_number": row.get("payer_id_number"),
                "contribution_title": row.get("contribution_title"),
                "amount_paid": round(row["amount_paid"], 2),
                "payment_method": row.get("payment_method"),
                "payment_status": row.get("payment_status"),
                "payment_day": row["payment_day"],
                "receipt_number": row["receipt_number"],
                "duplicate_reason": "Duplicate receipt number",
            }

    for group in composite_groups.values():
        if len(group) < 2:
            continue
        for row in group:
            duplicate_map.setdefault(
                row["id"],
                {
                    "id": row["id"],
                    "payer_name": row.get("payer_name"),
                    "payer_id_number": row.get("payer_id_number"),
                    "contribution_title": row.get("contribution_title"),
                    "amount_paid": round(row["amount_paid"], 2),
                    "payment_method": row.get("payment_method"),
                    "payment_status": row.get("payment_status"),
                    "payment_day": row["payment_day"],
                    "receipt_number": row["receipt_number"],
                    "duplicate_reason": "Duplicate payer/contribution/amount/day pattern",
                },
            )

    return sorted(duplicate_map.values(), key=lambda item: (item["payment_day"], item["id"]), reverse=True)


def detect_suspicious(payments: list[dict[str, Any]], duplicates: list[dict[str, Any]]) -> list[dict[str, Any]]:
    # Suspicious-record detection is intentionally heuristic. It flags unusual
    # rows for admin review; it does not try to make final fraud decisions.
    if not payments:
        return []

    duplicate_ids = {item["id"] for item in duplicates}
    amounts = [row["amount_paid"] for row in payments]
    q1 = quantile(amounts, 0.25)
    q3 = quantile(amounts, 0.75)
    iqr = q3 - q1
    upper_outlier = q3 + (1.5 * iqr) if iqr > 0 else max(amounts)

    same_day_counter = Counter((row["payer_db_id"], row["payment_day"]) for row in payments if row["payment_day"])
    suspicious = []

    for row in payments:
        reasons: list[str] = []
        if row["amount_paid"] > upper_outlier and upper_outlier > 0:
            reasons.append("Amount is an outlier")
        if same_day_counter[(row["payer_db_id"], row["payment_day"])] >= 4 and row["payment_day"]:
            reasons.append("High payment frequency for same payer/day")
        if row["id"] in duplicate_ids:
            reasons.append("Matches duplicate-payment pattern")
        if not row["receipt_number"] and str(row.get("payment_method") or "").lower() not in {"cash", ""}:
            reasons.append("Non-cash payment without receipt number")

        if reasons:
            suspicious.append(
                {
                    "id": row["id"],
                    "payer_name": row.get("payer_name"),
                    "payer_id_number": row.get("payer_id_number"),
                    "contribution_title": row.get("contribution_title"),
                    "amount_paid": round(row["amount_paid"], 2),
                    "payment_method": row.get("payment_method"),
                    "payment_status": row.get("payment_status"),
                    "payment_day": row["payment_day"],
                    "reason": "; ".join(dict.fromkeys(reasons)),
                }
            )

    return sorted(suspicious, key=lambda item: (item["payment_day"], item["id"]), reverse=True)


def build_trends(payments: list[dict[str, Any]]) -> tuple[list[dict[str, Any]], list[dict[str, Any]], list[dict[str, Any]], dict[str, Any]]:
    # Trend series are split into table-ready rows and Chart.js-ready payloads
    # so PHP can send them directly to the analytics view with minimal reshaping.
    now = datetime.now()
    daily_cutoff = now - timedelta(days=30)
    monthly_cutoff = now - timedelta(days=365)

    daily_revenue: dict[str, float] = defaultdict(float)
    daily_transactions: dict[str, int] = defaultdict(int)
    monthly_revenue: dict[tuple[int, int], float] = defaultdict(float)

    for row in payments:
        created_at = row["created_at_dt"]
        if not created_at:
            continue

        if created_at >= daily_cutoff:
            key = created_at.strftime("%Y-%m-%d")
            daily_revenue[key] += row["amount_paid"]
            daily_transactions[key] += 1

        if created_at >= monthly_cutoff:
            monthly_revenue[(created_at.year, created_at.month)] += row["amount_paid"]

    daily_revenue_rows = [{"date": key, "total": round(value, 2)} for key, value in sorted(daily_revenue.items())]
    daily_transactions_rows = [{"date": key, "count": value} for key, value in sorted(daily_transactions.items())]

    monthly_revenue_rows = []
    for (year, month), total in sorted(monthly_revenue.items()):
        monthly_revenue_rows.append(
            {
                "year": year,
                "month": month,
                "month_label": datetime(year, month, 1).strftime("%b %Y"),
                "total": round(total, 2),
            }
        )

    charts = {
        "daily_revenue": {
            "labels": [row["date"] for row in daily_revenue_rows],
            "data": [row["total"] for row in daily_revenue_rows],
        },
        "monthly_revenue": {
            "labels": [row["month_label"] for row in monthly_revenue_rows],
            "data": [row["total"] for row in monthly_revenue_rows],
        },
        "daily_transactions": {
            "labels": [row["date"] for row in daily_transactions_rows],
            "data": [row["count"] for row in daily_transactions_rows],
        },
    }

    return daily_revenue_rows, monthly_revenue_rows, daily_transactions_rows, charts


def analyze_payload(payload: dict[str, Any]) -> dict[str, Any]:
    # This is the main analytics pipeline used by both:
    # - the admin analytics page
    # - the review center alert summaries
    #
    # The returned structure is the contract expected by the PHP app.
    payments, contributions = normalize_payload(payload)
    valid_payments = [row for row in payments if str(row.get("payment_status") or "").lower() in VALID_PAYMENT_STATUSES]

    now = datetime.now()
    current_month_start = now.replace(day=1, hour=0, minute=0, second=0, microsecond=0)
    last_month_end = current_month_start - timedelta(seconds=1)
    last_month_start = last_month_end.replace(day=1, hour=0, minute=0, second=0, microsecond=0)

    this_month_sum = sum(row["amount_paid"] for row in valid_payments if row["created_at_dt"] and row["created_at_dt"] >= current_month_start)
    last_month_sum = sum(
        row["amount_paid"]
        for row in valid_payments
        if row["created_at_dt"] and last_month_start <= row["created_at_dt"] <= last_month_end
    )
    monthly_growth = ((this_month_sum - last_month_sum) / last_month_sum * 100) if last_month_sum else 0.0

    # Outstanding balances are reconstructed from what was paid per
    # payer+contribution pair versus the contribution amount.
    contribution_map = {row["id"]: row for row in contributions}
    balances: dict[tuple[int, int], float] = defaultdict(float)
    for row in valid_payments:
        balances[(row["payer_db_id"], row["contribution_id"])] += row["amount_paid"]

    outstanding_balance = 0.0
    for (_, contribution_id), total_paid in balances.items():
        contribution = contribution_map.get(contribution_id)
        if not contribution:
            continue
        outstanding_balance += max(contribution["amount"] - total_paid, 0.0)

    # Keep a raw status breakdown so the dashboard can still expose how many
    # rows fall into each stored payment_status value.
    by_status_counter: dict[str, dict[str, Any]] = defaultdict(lambda: {"count": 0, "total_amount": 0.0})
    for row in payments:
        status = str(row.get("payment_status") or "")
        by_status_counter[status]["count"] += 1
        by_status_counter[status]["total_amount"] += row["amount_paid"]
    by_status = [{"status": status, **values} for status, values in by_status_counter.items()]

    by_method_counter: dict[str, dict[str, Any]] = defaultdict(lambda: {"count": 0, "total_amount": 0.0})
    for row in valid_payments:
        method = str(row.get("payment_method") or "unknown")
        by_method_counter[method]["count"] += 1
        by_method_counter[method]["total_amount"] += row["amount_paid"]
    by_method = [{"payment_method": method, **values} for method, values in by_method_counter.items()]

    # Recent payments are trimmed here so the PHP UI can render a lightweight
    # "latest activity" section without extra sorting.
    recent_payments = sorted(valid_payments, key=lambda row: row["created_at_dt"] or datetime.min, reverse=True)[:10]
    recent_rows = [
        {
            "id": row["id"],
            "student_name": row.get("payer_name"),
            "contribution_title": row.get("contribution_title"),
            "amount": round(row["amount_paid"], 2),
            "payment_method": row.get("payment_method"),
            "status": row.get("payment_status"),
            "created_at": iso(row["created_at_dt"]),
        }
        for row in recent_payments
    ]

    # Aggregate payers from payment rows because "top payers" is based on what
    # has actually been collected, not on payer master data alone.
    top_payer_counter: dict[tuple[Any, ...], dict[str, Any]] = {}
    for row in valid_payments:
        key = (row["payer_db_id"], row.get("payer_name"), row.get("payer_id_number"), row.get("profile_picture"))
        bucket = top_payer_counter.setdefault(
            key,
            {
                "payer_db_id": row["payer_db_id"],
                "payer_name": row.get("payer_name"),
                "payer_id_number": row.get("payer_id_number"),
                "profile_picture": row.get("profile_picture"),
                "total_transactions": 0,
                "total_paid": 0.0,
            },
        )
        bucket["total_transactions"] += 1
        bucket["total_paid"] += row["amount_paid"]
    top_payers = sorted(top_payer_counter.values(), key=lambda item: item["total_paid"], reverse=True)[:10]

    duplicates = detect_duplicates(valid_payments)
    suspicious = detect_suspicious(valid_payments, duplicates)
    daily_revenue_rows, monthly_revenue_rows, daily_transactions_rows, charts = build_trends(valid_payments)
    profit = profit_summary(contributions)

    return {
        "generated_at": payload.get("generated_at") or datetime.now().isoformat(),
        "overview": {
            "total_revenue": round(sum(row["amount_paid"] for row in valid_payments), 2),
            "total_contributions": len(contributions),
            "active_contributors": len({row["payer_db_id"] for row in valid_payments}),
            "monthly_revenue": round(this_month_sum, 2),
            "monthly_growth": round(monthly_growth, 1),
            "total_profit": profit["total_profit"],
            "avg_profit_margin": profit["avg_profit_margin"],
            "total_outstanding_balance": round(outstanding_balance, 2),
            "duplicate_records": len(duplicates),
            "suspicious_records": len(suspicious),
        },
        "contributions": {
            "summary": profit,
            "top_profitable": top_profitable(contributions),
            "by_category": category_breakdown(contributions),
            "by_type": type_breakdown(contributions),
        },
        "payments": {
            "by_status": by_status,
            "by_method": by_method,
            "recent_payments": recent_rows,
            "top_payers": top_payers,
            "avg_transaction": round(mean([row["amount_paid"] for row in valid_payments]), 2) if valid_payments else 0.0,
            "duplicates": duplicates[:20],
            "suspicious": suspicious[:20],
        },
        "trends": {
            "daily_revenue": daily_revenue_rows,
            "monthly_revenue": monthly_revenue_rows,
            "daily_transactions": daily_transactions_rows,
        },
        "charts": charts,
    }


def build_report_lines(analysis: dict[str, Any]) -> list[str]:
    # Build a plain text report representation first; the simple PDF writer
    # below uses these lines directly.
    overview = analysis["overview"]
    payments = analysis["payments"]
    contributions = analysis["contributions"]
    lines = [
        "ClearPay Analytics Report",
        f"Generated: {analysis['generated_at']}",
        "",
        "Overview",
        f"Total Revenue: PHP {overview['total_revenue']:.2f}",
        f"Total Profit: PHP {overview['total_profit']:.2f}",
        f"Average Profit Margin: {overview['avg_profit_margin']:.2f}%",
        f"Monthly Revenue: PHP {overview['monthly_revenue']:.2f}",
        f"Monthly Growth: {overview['monthly_growth']:.1f}%",
        f"Outstanding Balance: PHP {overview['total_outstanding_balance']:.2f}",
        f"Duplicate Records: {overview['duplicate_records']}",
        f"Suspicious Records: {overview['suspicious_records']}",
        "",
        "Top Payers",
    ]

    for index, payer in enumerate(payments.get("top_payers", [])[:10], start=1):
        lines.append(
            f"{index}. {payer.get('payer_name') or 'Unknown'} [{payer.get('payer_id_number') or '-'}] - "
            f"PHP {as_float(payer.get('total_paid')):.2f} ({as_int(payer.get('total_transactions'))} txns)"
        )

    lines.extend(["", "Top Contributions"])
    for index, contribution in enumerate(contributions.get("top_profitable", [])[:10], start=1):
        lines.append(
            f"{index}. {contribution.get('title') or 'Unknown'} - PHP {as_float(contribution.get('profit_amount')):.2f} "
            f"({as_float(contribution.get('profit_margin')):.2f}% margin)"
        )

    lines.extend(["", "Suspicious Records"])
    if payments.get("suspicious"):
        for item in payments["suspicious"][:15]:
            lines.append(
                f"- Payment #{item['id']} {item.get('payer_name') or 'Unknown'} / "
                f"{item.get('contribution_title') or 'Unknown'}: {item.get('reason') or 'Flagged'}"
            )
    else:
        lines.append("- No suspicious records detected")

    lines.extend(["", "Duplicate Records"])
    if payments.get("duplicates"):
        for item in payments["duplicates"][:15]:
            lines.append(
                f"- Payment #{item['id']} {item.get('payer_name') or 'Unknown'} / "
                f"{item.get('contribution_title') or 'Unknown'}: {item.get('duplicate_reason') or 'Duplicate'}"
            )
    else:
        lines.append("- No duplicate records detected")

    return lines


def write_delimited_report(analysis: dict[str, Any], output_path: Path, delimiter: str) -> None:
    # CSV and Excel exports share the same tabular content. The only difference
    # is the delimiter: comma for CSV, tab for Excel-friendly output.
    rows = [
        ["ClearPay Analytics Report"],
        ["Generated", analysis["generated_at"]],
        [],
        ["Overview"],
        ["Metric", "Value"],
        ["Total Revenue", analysis["overview"]["total_revenue"]],
        ["Total Profit", analysis["overview"]["total_profit"]],
        ["Average Profit Margin", analysis["overview"]["avg_profit_margin"]],
        ["Monthly Revenue", analysis["overview"]["monthly_revenue"]],
        ["Monthly Growth", analysis["overview"]["monthly_growth"]],
        ["Outstanding Balance", analysis["overview"]["total_outstanding_balance"]],
        ["Duplicate Records", analysis["overview"]["duplicate_records"]],
        ["Suspicious Records", analysis["overview"]["suspicious_records"]],
        [],
        ["Top Payers"],
        ["Rank", "Name", "ID", "Total Paid", "Transactions"],
    ]

    for index, payer in enumerate(analysis["payments"].get("top_payers", []), start=1):
        rows.append([index, payer.get("payer_name"), payer.get("payer_id_number"), payer.get("total_paid"), payer.get("total_transactions")])

    rows.extend([[], ["Top Contributions"], ["Rank", "Title", "Category", "Profit", "Margin"]])
    for index, contribution in enumerate(analysis["contributions"].get("top_profitable", []), start=1):
        rows.append([index, contribution.get("title"), contribution.get("category"), contribution.get("profit_amount"), contribution.get("profit_margin")])

    rows.extend([[], ["Suspicious Records"], ["Payment ID", "Payer", "Contribution", "Reason"]])
    for item in analysis["payments"].get("suspicious", []):
        rows.append([item.get("id"), item.get("payer_name"), item.get("contribution_title"), item.get("reason")])

    rows.extend([[], ["Duplicate Records"], ["Payment ID", "Payer", "Contribution", "Reason"]])
    for item in analysis["payments"].get("duplicates", []):
        rows.append([item.get("id"), item.get("payer_name"), item.get("contribution_title"), item.get("duplicate_reason")])

    with output_path.open("w", newline="", encoding="utf-8-sig") as handle:
        writer = csv.writer(handle, delimiter=delimiter)
        writer.writerows(rows)


def escape_pdf_text(text: str) -> str:
    return text.replace("\\", "\\\\").replace("(", "\\(").replace(")", "\\)")


def write_simple_pdf(lines: list[str], output_path: Path) -> None:
    # This PDF writer is intentionally minimal and standard-library-only.
    # It avoids external dependencies like reportlab so the integration works
    # on a plain Python install.
    max_lines_per_page = 48
    pages = [lines[index : index + max_lines_per_page] for index in range(0, len(lines), max_lines_per_page)] or [[]]

    objects: list[bytes] = []

    def add_object(content: str | bytes) -> int:
        data = content.encode("latin-1", "replace") if isinstance(content, str) else content
        objects.append(data)
        return len(objects)

    catalog_num = add_object("<< /Type /Catalog /Pages 2 0 R >>")
    pages_num = add_object("<< /Type /Pages /Kids [] /Count 0 >>")
    font_num = add_object("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>")

    page_numbers: list[int] = []

    for page_lines in pages:
        stream_lines = ["BT", "/F1 11 Tf", "40 800 Td", "14 TL"]
        for line in page_lines:
            stream_lines.append(f"({escape_pdf_text(line)}) Tj")
            stream_lines.append("T*")
        stream_lines.append("ET")
        stream_body = "\n".join(stream_lines)
        content_num = add_object(f"<< /Length {len(stream_body.encode('latin-1', 'replace'))} >>\nstream\n{stream_body}\nendstream")
        page_num = add_object(
            f"<< /Type /Page /Parent {pages_num} 0 R /MediaBox [0 0 595 842] "
            f"/Resources << /Font << /F1 {font_num} 0 R >> >> /Contents {content_num} 0 R >>"
        )
        page_numbers.append(page_num)

    kids = " ".join(f"{page_num} 0 R" for page_num in page_numbers)
    objects[pages_num - 1] = f"<< /Type /Pages /Kids [{kids}] /Count {len(page_numbers)} >>".encode("latin-1")

    pdf = bytearray(b"%PDF-1.4\n")
    offsets = [0]
    for index, obj in enumerate(objects, start=1):
        offsets.append(len(pdf))
        pdf.extend(f"{index} 0 obj\n".encode("latin-1"))
        pdf.extend(obj)
        pdf.extend(b"\nendobj\n")

    xref_position = len(pdf)
    pdf.extend(f"xref\n0 {len(objects) + 1}\n".encode("latin-1"))
    pdf.extend(b"0000000000 65535 f \n")
    for offset in offsets[1:]:
        pdf.extend(f"{offset:010d} 00000 n \n".encode("latin-1"))
    pdf.extend(f"trailer\n<< /Size {len(objects) + 1} /Root {catalog_num} 0 R >>\nstartxref\n{xref_position}\n%%EOF".encode("latin-1"))

    output_path.write_bytes(pdf)


def write_report(analysis: dict[str, Any], output_path: Path, report_format: str) -> None:
    # Dispatch report generation based on the export requested by PHP.
    if report_format == "csv":
        write_delimited_report(analysis, output_path, ",")
        return
    if report_format == "excel":
        write_delimited_report(analysis, output_path, "\t")
        return
    if report_format == "pdf":
        write_simple_pdf(build_report_lines(analysis), output_path)
        return
    raise ValueError(f"Unsupported report format: {report_format}")


def load_payload(path: Path) -> dict[str, Any]:
    # Input payload is created by App\Services\PythonAnalyticsService and
    # written as JSON into CodeIgniter's writable cache directory.
    with path.open("r", encoding="utf-8") as handle:
        return json.load(handle)


def analyze_command(args: argparse.Namespace) -> int:
    # CLI mode used when PHP needs analytics JSON for dashboard/review pages.
    payload = load_payload(Path(args.input))
    analysis = analyze_payload(payload)
    Path(args.output).write_text(json.dumps(analysis, indent=2, ensure_ascii=False), encoding="utf-8")
    return 0


def report_command(args: argparse.Namespace) -> int:
    # CLI mode used when PHP needs an export file instead of JSON.
    payload = load_payload(Path(args.input))
    analysis = analyze_payload(payload)
    write_report(analysis, Path(args.output), args.format)
    return 0


def build_parser() -> argparse.ArgumentParser:
    # The worker exposes two subcommands:
    # - analyze: write analytics JSON
    # - report: write a report file (pdf/csv/excel)
    parser = argparse.ArgumentParser(description="ClearPay Python analytics worker")
    subparsers = parser.add_subparsers(dest="command", required=True)

    analyze_parser = subparsers.add_parser("analyze", help="Generate analytics JSON")
    analyze_parser.add_argument("--input", required=True)
    analyze_parser.add_argument("--output", required=True)
    analyze_parser.set_defaults(handler=analyze_command)

    report_parser = subparsers.add_parser("report", help="Generate report file")
    report_parser.add_argument("--input", required=True)
    report_parser.add_argument("--output", required=True)
    report_parser.add_argument("--format", required=True, choices=["pdf", "csv", "excel"])
    report_parser.set_defaults(handler=report_command)

    return parser


def main() -> int:
    # Keep the entrypoint thin: parse args, then hand off to the chosen mode.
    parser = build_parser()
    args = parser.parse_args()
    return args.handler(args)


if __name__ == "__main__":
    raise SystemExit(main())
