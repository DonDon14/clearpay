# PostgreSQL ENUM Migration Fixes

## Summary
All migrations have been updated to support PostgreSQL by converting ENUM types to VARCHAR with CHECK constraints.

## Fixed Migrations
- ✅ CreateUsersTable - role field
- ✅ CreateUserActivitiesTable - activity_type field
- ✅ CreateContributionsTable - status field
- ✅ CreatePaymentsTable - payment_status field
- ✅ CreateAnnouncementsTable - type, priority, target_audience, status fields
- ✅ CreateActivityLogsTable - activity_type, action, user_type, target_audience fields
- ✅ CreateRefundsTable - status, request_type fields
- ⚠️ CreatePaymentRequestsTable - status field (needs update)
- ⚠️ CreatePaymentMethodsTable - status field (needs update)
- ⚠️ CreateRefundMethodsTable - status field (needs update)
- ⚠️ CreateContributionCategoriesTable - status field (needs update)

## Pattern Used
All migrations now:
1. Detect PostgreSQL using: `strpos(strtolower($db->getPlatform()), 'postgre') !== false`
2. Use VARCHAR instead of ENUM for PostgreSQL
3. Add CHECK constraints after table creation for PostgreSQL

## Next Steps
Update the remaining 4 migrations with the same pattern.

