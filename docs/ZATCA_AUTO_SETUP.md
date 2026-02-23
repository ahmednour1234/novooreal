# ZATCA Automated Setup & Test Guide

This guide will walk you through setting up ZATCA Phase-2 e-invoicing with automated onboarding and test submission.

## Prerequisites

- Company information (VAT/TIN, company name, address)
- Access to ZATCA Fatoora portal
- At least one EGS (Electronic Generation System) unit configured

## Setup Steps

### Step 1: Fill Company Information

1. Navigate to **Settings > ZATCA Settings**
2. Go to the **Company Info** tab
3. Fill in the required fields:
   - **VAT/TIN Number**: Your company's VAT registration number (15 digits max)
   - **CR Number**: Commercial registration number (optional)
   - **Company Name (Arabic)**: Required
   - **Company Name (English)**: Required
   - **Address (Arabic & English)**: Optional
   - **Environment**: Select "Simulation" for testing or "Production" for live use
4. Click **Save**

### Step 2: Add EGS Unit

1. Go to the **EGS Units** tab
2. Click **Add New Unit**
3. Fill in the EGS unit details:
   - **EGS ID**: Unique identifier (e.g., EGS_01, EGS_02)
   - **Name**: Display name for the unit
   - **Type**: Branch or Cashier
   - **Branch**: Optional - link to a specific branch
4. Click **Save**

### Step 3: Generate CSR / Keys

1. In the **EGS Units** tab, find your EGS unit card
2. Click the **Generate CSR** button
3. Wait for the process to complete (you'll see a progress indicator)
4. Once complete, the CSR will be generated and stored securely
5. The CSR content is stored in `storage/app/zatca/certificates/{egs_id}/csr.pem`

**Note**: Private and public keys are automatically generated and stored securely in `storage/app/zatca/keys/{egs_id}/`

### Step 4: Get OTP from Fatoora Portal

1. Log in to the ZATCA Fatoora portal
2. Navigate to the EGS unit management section
3. Request an OTP (One-Time Password) for your EGS unit
4. The OTP will be sent to your registered contact method
5. **Important**: OTPs expire quickly, so have it ready before proceeding

### Step 5: Onboard (Simulation)

1. In the **EGS Units** tab, find your EGS unit
2. Click the **Onboard** button
3. A modal will open asking for:
   - **OTP**: Enter the OTP you received from Fatoora
   - **Environment**: Select "Simulation" for testing
4. Click **Onboard**
5. Wait for the process to complete
6. The system will:
   - Send the CSR to ZATCA with the OTP
   - Receive and store the Compliance CSID (encrypted)
   - Mark the EGS unit as active

**Security Note**: The OTP is never stored in the database. It is used immediately and then discarded.

### Step 6: Run Auto Test with Order ID

1. In the **EGS Units** tab, find your onboarded EGS unit
2. Click the **Test** button
3. A modal will open asking for:
   - **Order ID**: Enter the ID of an existing order to test
   - **Environment**: Select "Simulation"
4. Click **Test**
5. The system will:
   - Build the invoice XML from the order
   - Sign it with the stored keys
   - Submit it to ZATCA simulation environment
   - Display the result (success or failure with error details)

**Test Results**:
- **Success**: Shows ZATCA UUID, Long ID, and submission timestamp
- **Failure**: Shows error code and message with full response details

### Step 7: Switch to Production and Repeat Onboarding

Once you've successfully tested in simulation:

1. Go to **Company Info** tab
2. Change **Environment** to **Production**
3. Click **Save**
4. Go back to **EGS Units** tab
5. Click **Onboard** again for your EGS unit
6. Enter a new OTP from Fatoora (production OTPs are different)
7. Select **Production** environment in the modal
8. Click **Onboard**

**Important**: 
- Production CSID is stored separately from simulation CSID
- The system automatically uses the correct CSID based on the current environment setting
- In production mode, invoices are automatically submitted when orders are finalized

## Automated Submission

### Production Mode

When the environment is set to **Production**:
- Invoices are automatically submitted to ZATCA when orders are finalized
- The system uses the appropriate EGS unit based on order branch
- Submission happens asynchronously via queue jobs
- Results are stored in the `zatca_documents` table

### Simulation Mode

In **Simulation** mode:
- Automatic submission is disabled
- Use the **Test** button to manually test submissions
- This prevents accidental submissions during testing

## Troubleshooting

### CSR Generation Fails

- Ensure company information is saved
- Check file permissions for `storage/app/zatca/` directory
- Review logs in `storage/logs/laravel.log` (look for `zatca` channel)

### Onboarding Fails

- Verify OTP is correct and not expired
- Ensure CSR was generated successfully
- Check that company settings are complete
- Review error message in the job status indicator

### Test Submission Fails

- Verify the order exists and has valid data
- Ensure EGS unit is onboarded for the selected environment
- Check that order has required ZATCA fields (UUID, invoice number, etc.)
- Review error details in the response

### Job Status Not Updating

- Ensure queue worker is running: `php artisan queue:work`
- Check cache is working properly
- Review job logs in `storage/logs/laravel.log`

## Security Best Practices

1. **OTP Handling**: OTPs are never stored. They're used once and discarded.
2. **CSID Storage**: CSIDs are encrypted in the database using Laravel's encryption.
3. **Private Keys**: Private keys are encrypted and stored securely in storage.
4. **Logging**: Sensitive data (OTP, CSID, keys) is masked in logs.
5. **Access Control**: Only authorized admins can access ZATCA settings.

## Logging

All ZATCA operations are logged to the `zatca` channel:
- CSR generation
- Onboarding requests
- Invoice submissions
- Errors and failures

Logs are located in `storage/logs/laravel.log` and can be filtered by the `zatca` channel.

## Support

For issues or questions:
1. Check the logs first
2. Review error messages in the UI
3. Verify all prerequisites are met
4. Contact system administrator if needed
