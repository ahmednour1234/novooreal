<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Order;
use App\Services\ZATCAService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ZatcaValidatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_validate_invoice_requires_uuid(): void
    {
        $order = new Order();
        $order->invoice_number = 'INV-000001';
        $order->invoice_counter = 1;
        $order->currency_code = 'SAR';

        $validation = ZATCAService::validateZATCACompliance($order);

        $this->assertFalse($validation['valid']);
        $this->assertContains('UUID is required', $validation['errors']);
    }

    public function test_validate_invoice_requires_invoice_number(): void
    {
        $order = new Order();
        $order->uuid = 'test-uuid-123';
        $order->invoice_counter = 1;
        $order->currency_code = 'SAR';

        $validation = ZATCAService::validateZATCACompliance($order);

        $this->assertFalse($validation['valid']);
        $this->assertContains('Invoice number is required', $validation['errors']);
    }

    public function test_validate_invoice_requires_invoice_counter(): void
    {
        $order = new Order();
        $order->uuid = 'test-uuid-123';
        $order->invoice_number = 'INV-000001';
        $order->currency_code = 'SAR';

        $validation = ZATCAService::validateZATCACompliance($order);

        $this->assertFalse($validation['valid']);
        $this->assertContains('Invoice counter is required', $validation['errors']);
    }

    public function test_validate_invoice_requires_currency_code(): void
    {
        $order = new Order();
        $order->uuid = 'test-uuid-123';
        $order->invoice_number = 'INV-000001';
        $order->invoice_counter = 1;

        $validation = ZATCAService::validateZATCACompliance($order);

        $this->assertFalse($validation['valid']);
        $this->assertContains('Currency code is required', $validation['errors']);
    }

    public function test_validate_invoice_requires_sar_currency(): void
    {
        $order = new Order();
        $order->uuid = 'test-uuid-123';
        $order->invoice_number = 'INV-000001';
        $order->invoice_counter = 1;
        $order->currency_code = 'USD';

        $validation = ZATCAService::validateZATCACompliance($order);

        $this->assertFalse($validation['valid']);
        $this->assertContains('Currency code must be SAR for ZATCA compliance', $validation['errors']);
    }

    public function test_validate_invoice_passes_with_valid_data(): void
    {
        $order = new Order();
        $order->uuid = 'test-uuid-123';
        $order->invoice_number = 'INV-000001';
        $order->invoice_counter = 1;
        $order->currency_code = 'SAR';

        $validation = ZATCAService::validateZATCACompliance($order);

        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
    }
}
