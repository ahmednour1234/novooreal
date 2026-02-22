<?php

namespace App\Services;

use App\Models\Order;
use App\Models\CompanySetting;
use App\Models\ZatcaEgsUnit;

class ZatcaXmlService
{
    public function buildInvoiceXML(Order $order, ZatcaEgsUnit $egsUnit, string $invoiceType = 'standard'): string
    {
        $companySettings = CompanySetting::getSettings();
        if (!$companySettings) {
            throw new \RuntimeException('Company settings not found');
        }

        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        $invoice = $xml->createElement('Invoice');
        $xml->appendChild($invoice);

        $this->addInvoiceHeader($xml, $invoice, $order, $companySettings, $egsUnit);
        $this->addSellerInfo($xml, $invoice, $companySettings);
        $this->addBuyerInfo($xml, $invoice, $order);
        $this->addInvoiceLines($xml, $invoice, $order);
        $this->addInvoiceTotals($xml, $invoice, $order);

        return $xml->saveXML();
    }

    protected function addInvoiceHeader(\DOMDocument $xml, \DOMElement $invoice, Order $order, CompanySetting $settings, ZatcaEgsUnit $egsUnit): void
    {
        $invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');

        $this->addElement($xml, $invoice, 'cbc:ProfileID', 'reporting:1.0');
        $this->addElement($xml, $invoice, 'cbc:ID', $order->invoice_number ?? $order->id);
        $this->addElement($xml, $invoice, 'cbc:UUID', $order->uuid);
        $this->addElement($xml, $invoice, 'cbc:IssueDate', $order->date ? date('Y-m-d', strtotime($order->date)) : $order->created_at->format('Y-m-d'));
        $this->addElement($xml, $invoice, 'cbc:IssueTime', $order->created_at->format('H:i:s'));
        $this->addElement($xml, $invoice, 'cbc:InvoiceTypeCode', $this->getInvoiceTypeCode($order->type));
        $this->addElement($xml, $invoice, 'cbc:DocumentCurrencyCode', $order->currency_code ?? 'SAR');
    }

    protected function addSellerInfo(\DOMDocument $xml, \DOMElement $invoice, CompanySetting $settings): void
    {
        $party = $xml->createElement('cac:AccountingSupplierParty');
        $partyTaxScheme = $xml->createElement('cac:Party');
        $partyTaxSchemeNode = $xml->createElement('cac:PartyTaxScheme');
        $this->addElement($xml, $partyTaxSchemeNode, 'cbc:CompanyID', $settings->vat_tin);
        $partyTaxScheme->appendChild($partyTaxSchemeNode);
        $party->appendChild($partyTaxScheme);
        $invoice->appendChild($party);

        $partyLegalEntity = $xml->createElement('cac:PartyLegalEntity');
        $this->addElement($xml, $partyLegalEntity, 'cbc:RegistrationName', $settings->company_name_ar);
        $party->appendChild($partyLegalEntity);
    }

    protected function addBuyerInfo(\DOMDocument $xml, \DOMElement $invoice, Order $order): void
    {
        $party = $xml->createElement('cac:AccountingCustomerParty');
        $partyNode = $xml->createElement('cac:Party');

        if ($order->customer) {
            $partyLegalEntity = $xml->createElement('cac:PartyLegalEntity');
            $customerName = trim(($order->customer->f_name ?? '') . ' ' . ($order->customer->l_name ?? ''));
            $this->addElement($xml, $partyLegalEntity, 'cbc:RegistrationName', $customerName ?: 'Customer');
            $partyNode->appendChild($partyLegalEntity);
        }

        $party->appendChild($partyNode);
        $invoice->appendChild($party);
    }

    protected function addInvoiceLines(\DOMDocument $xml, \DOMElement $invoice, Order $order): void
    {
        $details = $order->details;
        foreach ($details as $detail) {
            $line = $xml->createElement('cac:InvoiceLine');
            $this->addElement($xml, $line, 'cbc:ID', $detail->id);
            $this->addElement($xml, $line, 'cbc:InvoicedQuantity', $detail->quantity, ['unitCode' => 'C62']);
            $this->addElement($xml, $line, 'cbc:LineExtensionAmount', number_format($detail->price * $detail->quantity, 2, '.', ''), ['currencyID' => $order->currency_code ?? 'SAR']);

            $item = $xml->createElement('cac:Item');
            $this->addElement($xml, $item, 'cbc:Name', $detail->product_details['name'] ?? 'Product');
            $line->appendChild($item);

            $price = $xml->createElement('cac:Price');
            $this->addElement($xml, $price, 'cbc:PriceAmount', number_format($detail->price, 2, '.', ''), ['currencyID' => $order->currency_code ?? 'SAR']);
            $line->appendChild($price);

            $invoice->appendChild($line);
        }
    }

    protected function addInvoiceTotals(\DOMDocument $xml, \DOMElement $invoice, Order $order): void
    {
        $taxExclusiveAmount = $order->order_amount - ($order->total_tax ?? 0);
        $taxInclusiveAmount = $order->order_amount;

        $this->addElement($xml, $invoice, 'cbc:TaxExclusiveAmount', number_format($taxExclusiveAmount, 2, '.', ''), ['currencyID' => $order->currency_code ?? 'SAR']);
        $this->addElement($xml, $invoice, 'cbc:TaxInclusiveAmount', number_format($taxInclusiveAmount, 2, '.', ''), ['currencyID' => $order->currency_code ?? 'SAR']);

        if ($order->total_tax > 0) {
            $taxTotal = $xml->createElement('cac:TaxTotal');
            $taxAmount = $xml->createElement('cbc:TaxAmount', number_format($order->total_tax, 2, '.', ''), ['currencyID' => $order->currency_code ?? 'SAR']);
            $taxTotal->appendChild($taxAmount);
            $invoice->appendChild($taxTotal);
        }

        $legalMonetaryTotal = $xml->createElement('cac:LegalMonetaryTotal');
        $this->addElement($xml, $legalMonetaryTotal, 'cbc:LineExtensionAmount', number_format($taxExclusiveAmount, 2, '.', ''), ['currencyID' => $order->currency_code ?? 'SAR']);
        $this->addElement($xml, $legalMonetaryTotal, 'cbc:TaxExclusiveAmount', number_format($taxExclusiveAmount, 2, '.', ''), ['currencyID' => $order->currency_code ?? 'SAR']);
        $this->addElement($xml, $legalMonetaryTotal, 'cbc:TaxInclusiveAmount', number_format($taxInclusiveAmount, 2, '.', ''), ['currencyID' => $order->currency_code ?? 'SAR']);
        $this->addElement($xml, $legalMonetaryTotal, 'cbc:PayableAmount', number_format($taxInclusiveAmount, 2, '.', ''), ['currencyID' => $order->currency_code ?? 'SAR']);
        $invoice->appendChild($legalMonetaryTotal);
    }

    protected function addElement(\DOMDocument $xml, \DOMElement $parent, string $name, ?string $value, array $attributes = []): void
    {
        $element = $xml->createElement($name, $value ?? '');
        foreach ($attributes as $attrName => $attrValue) {
            $element->setAttribute($attrName, $attrValue);
        }
        $parent->appendChild($element);
    }

    protected function getInvoiceTypeCode(int $orderType): string
    {
        $typeMap = [
            1 => '0100000',
            4 => '0100000',
            7 => '0200000',
            12 => '0200000',
            24 => '0200000',
        ];

        return $typeMap[$orderType] ?? '0100000';
    }
}
