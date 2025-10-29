<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PaymentMethodCustomInstructionsSeeder extends Seeder
{
    public function run()
    {
        // Update existing GCash payment method with custom instructions
        $gcashInstructions = '
            <div class="row">
                <div class="col-12">
                    <h6><i class="fas fa-qrcode me-2"></i>{method_name} QR Code Payment</h6>
                    
                    <!-- QR Code Display -->
                    <div class="text-center mb-4">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-qrcode me-2"></i>Scan QR Code to Pay
                                </h6>
                                <div class="qr-code-container mb-3">
                                    <img src="{qr_code_path}" 
                                         alt="{method_name} QR Code" 
                                         style="width: 200px; height: 200px; cursor: pointer;" 
                                         class="img-fluid border rounded shadow-sm" 
                                         onclick="showQRCodeFullscreen(\'{qr_code_path}\')" 
                                         onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'block\';" 
                                         title="Click to view full screen">
                                    <div class="qr-placeholder bg-light p-4 rounded" style="display: none; width: 200px; height: 200px; margin: 0 auto;">
                                        <div class="text-muted text-center">
                                            <i class="fas fa-qrcode fa-3x mb-2"></i><br>
                                            <small>QR Code Loading...</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Payment Details -->
                                <div class="alert alert-info mb-3">
                                    <div class="row text-start">
                                        <div class="col-md-6">
                                            <strong>Amount:</strong> ₱{amount}<br>
                                            <strong>Recipient:</strong> {account_name}<br>
                                            <strong>Reference:</strong> {reference_prefix}-{timestamp}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{method_name} Number:</strong> {account_number}<br>
                                            <strong>Account Name:</strong> {account_name}<br>
                                            <button type="button" class="btn btn-outline-primary btn-sm mt-1" onclick="copyToClipboard(\'{reference_prefix}-{timestamp}\')">
                                                <i class="fas fa-copy me-1"></i>Copy Reference
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="showQRCodeFullscreen(\'{qr_code_path}\')">
                                        <i class="fas fa-expand me-1"></i>View Full Screen
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="showManualTransfer(\'{amount}\')">
                                        <i class="fas fa-hand-holding-usd me-1"></i>Manual Transfer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instructions Toggle -->
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-link btn-sm" data-bs-toggle="collapse" data-bs-target="#paymentInstructions" aria-expanded="false" aria-controls="paymentInstructions">
                            <i class="fas fa-info-circle me-1"></i>Show Payment Instructions
                        </button>
                    </div>
                    
                    <!-- Collapsible Instructions -->
                    <div class="collapse" id="paymentInstructions">
                        <div class="card card-body mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="alert alert-success mb-2">
                                        <h6 class="mb-2"><i class="fas fa-mobile-alt me-1"></i>Two Devices (Recommended)</h6>
                                        <ol class="mb-0 small">
                                            <li>Open {method_name} app on your phone</li>
                                            <li>Tap "Scan QR"</li>
                                            <li>Scan the QR code above</li>
                                            <li>Enter amount: <strong>₱{amount}</strong></li>
                                            <li>Add reference: <strong>{reference_prefix}-{timestamp}</strong></li>
                                            <li>Confirm payment</li>
                                        </ol>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-warning mb-2">
                                        <h6 class="mb-2"><i class="fas fa-mobile-alt me-1"></i>Single Device</h6>
                                        <ol class="mb-0 small">
                                            <li>Take a screenshot of the QR code</li>
                                            <li>Open {method_name} app</li>
                                            <li>Tap "Scan QR" → "From Gallery"</li>
                                            <li>Select the screenshot</li>
                                            <li>Enter amount: <strong>₱{amount}</strong></li>
                                            <li>Add reference: <strong>{reference_prefix}-{timestamp}</strong></li>
                                            <li>Confirm payment</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Additional Tips -->
                            <div class="alert alert-light border">
                                <h6 class="mb-2"><i class="fas fa-lightbulb me-1"></i>Pro Tips</h6>
                                <ul class="mb-0 small">
                                    <li>Make sure your {method_name} account has sufficient balance</li>
                                    <li>Double-check the amount before confirming</li>
                                    <li>Save the transaction receipt for your records</li>
                                    <li>Upload the payment proof below after completing the transaction</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';

        // Update GCash with custom instructions
        $this->db->table('payment_methods')
            ->where('name', 'GCash')
            ->update([
                'account_number' => '0917-123-4567',
                'account_name' => 'ClearPay School',
                'qr_code_path' => 'images/gcashcodesample.png',
                'custom_instructions' => $gcashInstructions,
                'reference_prefix' => 'CP',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Update PayMaya with custom instructions
        $paymayaInstructions = '
            <div class="alert alert-info">
                <h6><i class="fas fa-mobile-alt me-2"></i>{method_name} Payment Instructions</h6>
                <p class="mb-2">Please prepare the following for {method_name} payment:</p>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Amount:</strong> ₱{amount}<br>
                        <strong>Payment Type:</strong> {method_name}<br>
                        <strong>Reference:</strong> {reference_prefix}-{timestamp}
                    </div>
                    <div class="col-md-6">
                        <strong>Account Number:</strong> {account_number}<br>
                        <strong>Account Name:</strong> {account_name}<br>
                        <strong>Instructions:</strong><br>
                        <ul class="mb-0 small">
                            <li>Open {method_name} app</li>
                            <li>Tap "Send Money"</li>
                            <li>Enter amount: ₱{amount}</li>
                            <li>Add reference: {reference_prefix}-{timestamp}</li>
                            <li>Confirm payment</li>
                        </ul>
                    </div>
                </div>
            </div>
        ';

        $this->db->table('payment_methods')
            ->where('name', 'PayMaya')
            ->update([
                'account_number' => '0918-987-6543',
                'account_name' => 'ClearPay School',
                'custom_instructions' => $paymayaInstructions,
                'reference_prefix' => 'CP',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Update Bank Transfer with custom instructions
        $bankTransferInstructions = '
            <div class="alert alert-info">
                <h6><i class="fas fa-university me-2"></i>{method_name} Instructions</h6>
                <p class="mb-2">Please prepare the following for {method_name}:</p>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Amount:</strong> ₱{amount}<br>
                        <strong>Payment Type:</strong> {method_name}<br>
                        <strong>Reference:</strong> {reference_prefix}-{timestamp}
                    </div>
                    <div class="col-md-6">
                        <strong>Account Number:</strong> {account_number}<br>
                        <strong>Account Name:</strong> {account_name}<br>
                        <strong>Instructions:</strong><br>
                        <ul class="mb-0 small">
                            <li>Go to your bank\'s online banking</li>
                            <li>Transfer amount: ₱{amount}</li>
                            <li>Add reference: {reference_prefix}-{timestamp}</li>
                            <li>Upload proof of transfer below</li>
                        </ul>
                    </div>
                </div>
            </div>
        ';

        $this->db->table('payment_methods')
            ->where('name', 'Bank Transfer')
            ->update([
                'account_number' => '1234-5678-9012',
                'account_name' => 'ClearPay School',
                'custom_instructions' => $bankTransferInstructions,
                'reference_prefix' => 'CP',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Update Cash with custom instructions
        $cashInstructions = '
            <div class="alert alert-info">
                <h6><i class="fas fa-money-bill me-2"></i>{method_name} Payment Instructions</h6>
                <p class="mb-2">Please prepare the exact amount for {method_name} payment:</p>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Amount:</strong> ₱{amount}<br>
                        <strong>Payment Type:</strong> {method_name}<br>
                        <strong>Reference:</strong> {reference_prefix}-{timestamp}
                    </div>
                    <div class="col-md-6">
                        <strong>Location:</strong> {account_name}<br>
                        <strong>Instructions:</strong><br>
                        <ul class="mb-0 small">
                            <li>Prepare exact amount</li>
                            <li>Bring valid ID</li>
                            <li>Get official receipt</li>
                            <li>Keep receipt for records</li>
                        </ul>
                    </div>
                </div>
            </div>
        ';

        $this->db->table('payment_methods')
            ->where('name', 'Cash')
            ->update([
                'account_name' => 'Main Campus Office',
                'custom_instructions' => $cashInstructions,
                'reference_prefix' => 'CP',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        echo "Custom payment instructions have been added to all payment methods.\n";
    }
}