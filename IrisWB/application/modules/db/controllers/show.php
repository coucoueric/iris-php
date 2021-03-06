<?php

namespace modules\db\controllers;

/*
 * This file is part of IRIS-PHP.
 *
 * IRIS-PHP is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * IRIS-PHP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with IRIS-PHP.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * @copyright 2012 Jacques THOORENS
 *
 * 
 */

/**
 * 
 * Test of basic database operations
 * 
 * @author jacques
 * @license not defined
 */
class show extends _db {

    protected function _init() {
        $this->_entityManager = \models\_invoiceManager::DefaultEntityManager();
        $this->__action = "show";
        $this->dbState()->validateDB();
    }

    /**
     * Displays all the invoices (with full details)
     * 
     * @param type $dbType
     */
    public function invoicesAction() {
        $tInvoices = \models\TInvoices::GetEntity($this->_entityManager);
        $invoices = $tInvoices->fetchAll();
        foreach ($invoices as $invoice) {
            $invs[] = $this->_readInvoice($invoice);
        }
        $this->__invoices = $invs;
        /* @var $container \Dojo\views\helpers\TabContainer */
        $container = $this->callViewHelper('dojo_tabContainer', 'container');
        $container->setDim(300, 700);
    }

    /**
     * Converts an invoice to an array, reading the orders and 
     * products corresponding to them
     * 
     * @param type $invoice
     * @return type
     */
    private function _readInvoice($invoice) {
        $inv = [];
        $inv['id'] = $invoice->id;
        $date = new \Iris\Time\Date($invoice->InvoiceDate);
        $inv['Date'] = $invoice->InvoiceDate;
        $inv['CustName'] = $invoice->_at_customer_id->Name;
        $orders = $invoice->_children_orders__invoice_id;
        foreach ($orders as $order) {
            $ord['Qty'] = $order->Quantity;
            $product = $order->_at_product_id;
            $ord['Description'] = $product->Description;
            $ord['UP'] = $product->Price;
            $inv['Orders'][] = $ord;
        }
        return $inv;
    }

    /**
     * Displays all the customers
     */
    public function customersAction() {
        $tCustomers = \models\TCustomers::GetEntity($this->_entityManager);
        $customers = $tCustomers->fetchAll();
        foreach ($customers as $customer) {
            $cust['Name'] = $customer->Name;
            $invoices = $customer->_children_invoices__customer_id;
            // foreign key not necessary here
            //$invoices = $customer->_children_invoices;
            $invs = [];
            foreach ($invoices as $invoice) {
                $date = new \Iris\Time\Date($invoice->InvoiceDate);
                $invs[] = array('Number' => $invoice->id, 'Date' => $date->toString('d M Y'));
            }
            $cust["Inv"] = $invs;
            $custs[] = $cust;
        }
        $this->__customers = $custs;
    }

    /**
     * Displays all the customers
     */
    public function productsAction() {
        $tProducts = \models\TProducts::GetEntity($this->_entityManager);
        $products = $tProducts->fetchAll();
        foreach ($products as $products) {
            $prods[] = $this->_readProduct($products);
        }
        $this->__products = $prods;
        /* @var $container \Dojo\views\helpers\TabContainer */
        $container = $this->callViewHelper('dojo_tabContainer', 'container');
        $container->setDim(300, 700);
    }

    private function _readProduct($product) {
        $prod['Description'] = $product->Description;
        $prod['Price'] = $product->Price;
        $orders = $product->_children_orders__product_id;
        $invs = [];
        foreach ($orders as $order) {
            $invoice = $order->_at_invoice_id;
            $invs[] = [
                'Quantity' => $order->Quantity,
                'Number' => $invoice->id,
                'Date' => $invoice->Date,
                'CustomerName' => $invoice->_at_customer_id->Name,
            ];
        }
        $prod['Invoices'] = $invs;
        return $prod;
    }

    public function eventsAction() {
        $tEvents = \models\TEvents::GetEntity($this->_entityManager);
        $events = $tEvents->fetchAll();
        foreach ($events as $event) {
            $ord = [];
            $ev['Key1'] = $event->invoice_id;
            $ev['Key2'] = $event->product_id;
            $ev['Moment'] = $event->Moment;
            $ev['Description'] = $event->Description;
            $order = $event->_at_invoice_id__product_id;
            $ord['Qty'] = $order->Quantity;
            $ord['Product'] = $order->_at_product_id->Description;
            $ord['Invoice'] = $order->_at_invoice_id->id;
            $ev['Order'] = $ord;
            $evs[] = $ev;
        }
        $this->__events = $evs;
    }

    public function ordersAction() {
        $tOrders = \models\TOrders::GetEntity();
        $orders = $tOrders->fetchAll();
        foreach ($orders as $order) {
            $evs = [];
            $invoice = $order->_at_invoice_id;
            $ord['InvNum'] = $order->invoice_id . '/' . $order->product_id;
            $date = new \Iris\Time\Date($invoice->InvoiceDate);
            $ord['Date'] = $date;
            $ord['Description'] = $order->_at_product_id->Description;
            $events = $order->_children_events__invoice_id__product_id;
            foreach ($events as $event) {
                $ev = $event->Moment . ': ' . $event->Description;
                $evs[] = $ev;
            }
            $ord['Events'] = $evs;
            $ords[] = $ord;
        }
        $this->__orders = $ords;
        /* @var $container \Dojo\views\helpers\TabContainer */
        $container = $this->callViewHelper('dojo_tabContainer', 'container');
        $container->setDim(300, 700);
    }

}
