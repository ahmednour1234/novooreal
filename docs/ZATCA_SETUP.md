# ZATCA Integration Setup Guide

This guide will help you set up ZATCA (Saudi e-invoicing) integration for your ERP installation in approximately 10 minutes.

## Prerequisites

- Laravel application with database configured
- Access to ZATCA Fatoora portal
- Company VAT/TIN number
- Company registration (CR) number

## Quick Start (10-Minute Setup)

### Step 1: Run Database Migrations

```bash
php artisan migrate
```

This will create the necessary tables:
- `company_settings`
- `zatca_egs_units`
- `zatca_documents`
- `zatca_audit_logs`

### Step 2: Run Setup Wizard

```bash
php artisan zatca:setup --env=simulation
```

The wizard will guide you through:

1. **Company Information**
   - VAT/TIN Number
   - CR Number
   - Company Name (Arabic & English)
   - Address (Arabic & English)

2. **EGS Units Creation**
   - Number of EGS units (branches/cashiers)
   - EGS ID for each unit (e.g., EGS_01, EGS_02)
   - Name and type for each unit

3. **Key Generation**
   - Automatic generation of RSA key pairs
   - Generation of Certificate Signing Requests (CSR)

4. **Onboarding**
   - Upload CSR to ZATCA Fatoora portal
   - Enter OTP received from portal
   - Store Compliance CSID

5. **Test Submission**
   - Validation test
   - Sample invoice submission

### Step 3: Verify Setup

Check that your EGS units are active:

```bash
php artisan tinker
>>> \App\Models\ZatcaEgsUnit::where('status', 'active')->get();
```

## Command Reference

### Setup Commands

#### `zatca:setup`
Interactive setup wizard for initial configuration.

```bash
php artisan zatca:setup --env=simulation
php artisan zatca:setup --env=production
```

**Options:**
- `--env`: Environment (simulation/production), default: simulation

#### `zatca:csr`
Generate or regenerate CSR for an EGS unit.

```bash
php artisan zatca:csr --egs=EGS_01 --env=simulation
```

**Options:**
- `--egs`: EGS Unit ID (required)
- `--env`: Environment (simulation/production), default: simulation

**Example:**
```bash
php artisan zatca:csr --egs=EGS_01
```

#### `zatca:onboard`
Onboard an EGS unit with ZATCA using OTP.

```bash
php artisan zatca:onboard --egs=EGS_01 --env=simulation --otp=123456
```

**Options:**
- `--egs`: EGS Unit ID (required)
- `--env`: Environment (simulation/production), default: simulation
- `--otp`: OTP from ZATCA portal (optional, will prompt if not provided)

**Example:**
```bash
php artisan zatca:onboard --egs=EGS_01 --otp=123456
```

### Invoice Commands

#### `zatca:validate-invoice`
Validate an invoice against ZATCA rules (local validation).

```bash
php artisan zatca:validate-invoice --invoice_id=123
```

**Options:**
- `--invoice_id`: Order/Invoice ID (required)

**Example:**
```bash
php artisan zatca:validate-invoice --invoice_id=123
```

#### `zatca:submit`
Manually submit an invoice to ZATCA.

```bash
php artisan zatca:submit --invoice_id=123
```

**Options:**
- `--invoice_id`: Order/Invoice ID (required)

**Example:**
```bash
php artisan zatca:submit --invoice_id=123
```

### Compliance Commands

#### `zatca:compliance-pack`
Generate compliance report pack for a date range.

```bash
php artisan zatca:compliance-pack --egs=EGS_01 --from=2024-01-01 --to=2024-01-31
```

**Options:**
- `--egs`: EGS Unit ID (required)
- `--from`: Start date (YYYY-MM-DD), default: 30 days ago
- `--to`: End date (YYYY-MM-DD), default: today

**Example:**
```bash
php artisan zatca:compliance-pack --egs=EGS_01 --from=2024-01-01 --to=2024-01-31
```

## Moving from Simulation to Production

### Step 1: Complete Compliance Testing

Ensure all invoices are successfully submitted in simulation environment and compliance pack is generated.

### Step 2: Request Production CSID

1. Generate a new CSR for production:
   ```bash
   php artisan zatca:csr --egs=EGS_01 --env=production
   ```

2. Upload the CSR to ZATCA Fatoora portal (production environment)

3. Get OTP from portal

4. Onboard with production OTP:
   ```bash
   php artisan zatca:onboard --egs=EGS_01 --env=production --otp=XXXXXX
   ```

### Step 3: Update Environment

Update company settings to production:

```bash
php artisan tinker
>>> $settings = \App\Models\CompanySetting::first();
>>> $settings->environment = 'production';
>>> $settings->save();
```

Or update directly in database:
```sql
UPDATE company_settings SET environment = 'production';
```

### Step 4: Verify Production Setup

Test with a sample invoice:
```bash
php artisan zatca:submit --invoice_id=123
```

## How It Works

### Automatic Invoice Submission

When an invoice is finalized (saved), the system automatically:

1. **Event Trigger**: `InvoiceFinalized` event is dispatched
2. **Listener**: `ProcessZatcaInvoice` listener checks if ZATCA is enabled
3. **Job Queue**: `SubmitZatcaInvoiceJob` is queued for async processing
4. **Processing**:
   - Build XML from invoice data
   - Sign XML with EGS unit private key
   - Submit to ZATCA API
   - Store response in `zatca_documents` table
   - Update invoice with submission status

### Invoice Types

- **Standard Invoices**: Order types 4 (sales), 12 (purchase returns)
- **Simplified Invoices**: Order type 1 (cash sales)

### EGS Unit Selection

The system selects an EGS unit based on:
1. Invoice branch ID (if available)
2. First active EGS unit (fallback)

## Storage

### Key Storage

Keys are stored in:
- **Local**: `storage/app/zatca/keys/{egs_id}/`
- **S3**: If configured via `ZATCA_STORAGE_DISK=s3`

Private keys are encrypted at rest using Laravel's encryption.

### Certificates

Certificates and CSRs are stored in:
- **Local**: `storage/app/zatca/certificates/{egs_id}/`
- **S3**: If configured via `ZATCA_STORAGE_DISK=s3`

## Configuration

### Environment Variables

Add to your `.env` file:

```env
ZATCA_ENVIRONMENT=simulation
ZATCA_STORAGE_DISK=local
ZATCA_QUEUE_CONNECTION=database
ZATCA_QUEUE_NAME=zatca

# Optional: S3 configuration for key storage
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your_bucket
```

### Config File

Configuration is in `config/zatca.php`. Key settings:

- **API Endpoints**: Simulation and production URLs
- **Storage**: Disk and paths for keys/certificates
- **Retry Logic**: Max attempts and backoff strategy
- **Queue**: Queue connection and name

## Troubleshooting

### Issue: "EGS unit not found or not onboarded"

**Solution:**
1. Check EGS unit status:
   ```bash
   php artisan tinker
   >>> \App\Models\ZatcaEgsUnit::all();
   ```
2. Ensure at least one EGS unit is active and onboarded
3. Run `zatca:setup` if no EGS units exist

### Issue: "Company settings not found"

**Solution:**
Run the setup wizard:
```bash
php artisan zatca:setup --env=simulation
```

### Issue: "CSID not found"

**Solution:**
1. Check if EGS unit is onboarded:
   ```bash
   php artisan tinker
   >>> $egs = \App\Models\ZatcaEgsUnit::where('egs_id', 'EGS_01')->first();
   >>> $egs->isOnboarded();
   ```
2. If not onboarded, run:
   ```bash
   php artisan zatca:onboard --egs=EGS_01 --otp=XXXXXX
   ```

### Issue: Invoice submission fails

**Solution:**
1. Check job queue:
   ```bash
   php artisan queue:work --queue=zatca
   ```
2. Check audit logs:
   ```bash
   php artisan tinker
   >>> \App\Models\ZatcaAuditLog::latest()->take(10)->get();
   ```
3. Check invoice document:
   ```bash
   php artisan tinker
   >>> \App\Models\ZatcaDocument::where('order_id', 123)->first();
   ```

### Issue: Keys not found

**Solution:**
Regenerate keys and CSR:
```bash
php artisan zatca:csr --egs=EGS_01
```

## Audit Logging

All ZATCA operations are logged in `zatca_audit_logs` table:

- CSID requests
- Invoice submissions
- Compliance pack generation
- Errors and failures

View logs:
```bash
php artisan tinker
>>> \App\Models\ZatcaAuditLog::latest()->take(20)->get();
```

## Security Best Practices

1. **Encryption**: Private keys are encrypted at rest
2. **S3 Storage**: Use S3 for production key storage
3. **Access Control**: Restrict access to ZATCA commands
4. **Logging**: Sensitive data is masked in logs
5. **Backup**: Regularly backup keys and certificates

## Support

For issues or questions:
1. Check audit logs in database
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check queue failed jobs: `php artisan queue:failed`

## Additional Resources

- ZATCA Fatoora Portal: https://fatoora.zatca.gov.sa
- ZATCA Documentation: https://zatca.gov.sa
- Laravel Queue Documentation: https://laravel.com/docs/queues
