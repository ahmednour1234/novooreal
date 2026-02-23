<?php

namespace App\Services\Zatca;

use App\Models\Order;
use App\Models\ZatcaEgsUnit;
use App\Services\ZatcaXmlService;

class ZatcaOrderXmlBuilder
{
    protected ZatcaXmlService $xmlService;

    public function __construct(ZatcaXmlService $xmlService)
    {
        $this->xmlService = $xmlService;
    }

    /**
     * Build ZATCA UBL XML from Order
     * 
     * @param Order $order
     * @param ZatcaEgsUnit $egsUnit
     * @param string $invoiceType 'standard' or 'simplified'
     * @return string XML content
     */
    public function buildXml(Order $order, ZatcaEgsUnit $egsUnit, string $invoiceType = 'standard'): string
    {
        return $this->xmlService->buildInvoiceXML($order, $egsUnit, $invoiceType);
    }

    /**
     * Determine invoice type from order type
     */
    public function getInvoiceType(Order $order): string
    {
        $standardTypes = config('zatca.invoice_types.standard', [4, 12]);
        return in_array($order->type, $standardTypes) ? 'standard' : 'simplified';
    }
}
