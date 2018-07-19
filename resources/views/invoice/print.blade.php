<!DOCTYPE html>
<html>
<head>
    <title>{{env('APP_NAME')}}</title>
    <meta charset="utf-8" />
    @if(isset($action) && $action == 'print')
        <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
    @endif
    <style>
    @if(isset($action) && $action == 'print')
        *{font-family:'Open Sans','Verdana';font-size: 14px;text-align: justify;}
    @else
        *{font-family:'Helvetica';font-size: 14px;text-align: justify;}
    @endif
    body{width:auto; max-width:800px;margin:0 auto;,font-size:14px;}
    h2{font-size: 16px;font-weight: bold;}
    table.table-head th{font-size: 14px; font-weight: bold;text-align: right;}
    table.table-head td{font-size: 14px;text-align: right;}

    table.fancy-detail {  font-size:14px; border-collapse: collapse;  width:100%;  margin:0 auto;}
    table.fancy-detail th{  background:#F5F5F5; border-bottom: 1px #2e2e2e solid;  padding: 0.5em;  padding-left:10px; vertical-align:top;text-align: left;}
    table.fancy-detail th, table.fancy-detail td  {  padding: 0.5em;  padding-left:10px; border-bottom:1px solid #2e2e2e;text-align: left;}
    table.fancy-detail caption {  margin-left: inherit;  margin-right: inherit;}
    table.fancy-detail tr:hover{}

    </style>
</head>
<body>
    <div style="background-color: #F5F5F5; padding:5px;">
        <div style="padding:10px;background: #ffffff;">
            <table border="0" style="width:100%;margin-top: 20px;height: 100px;">
            <tr>
                <td style="width:40%;vertical-align: top;">
                    {!! getCompanyLogo() !!}
                </td>
                <td style="width:60%;vertical-align: top;">
                    <table align="right" class="table-head">
                        @if($invoice->reference_number)
                        <tr>
                            <th>{{trans('invoice.reference_number')}}</th><td>{{$invoice->reference_number}}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>{{trans('invoice.invoice_number')}}</th><td>{{$invoice->invoice_number}}</td>
                        </tr>
                        <tr>
                            <th>{{trans('invoice.date')}}</th><td>{{showDate($invoice->date)}}</td>
                        </tr>
                        @if($invoice->due_date != 'no_due_date')
                        <tr>
                            <th>{{trans('invoice.due_date')}}</th><td>{{showDate($invoice->due_date_detail)}}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
            </table>
            <table border="0" style="width:100%;">
                <tr>
                    <td colspan="3"><hr></td>
                </tr>
                <tr>
                    <td style="width:40%; vertical-align: top;">
                        <p style="font-size:14px;"><span style="font-size:16px; font-weight: bold;">{{config('config.company_name')}}</span><br />
                        {!! (config('config.email')) ? (trans('configuration.email').' : '.config('config.email').' <br />') : ''!!}
                        {!! (config('config.phone')) ? (trans('configuration.phone').' : '.config('config.phone').' <br />') : ''!!}
                        {!! config('config.address_line_1') ? (config('config.address_line_1').' <br />') : ''!!}
                        {!! config('config.address_line_2') ? (config('config.address_line_2').' <br />') : ''!!}
                        {!! config('config.city') ? (config('config.city').' <br />') : ''!!}
                        {!! config('config.state') ? (config('config.state').' <br />') : ''!!}
                        {!! config('config.zipcode') ? (config('config.zipcode').' <br />') : ''!!}
                        {!! config('config.country') ?  config('config.country') : ''!!}
                        </p>
                    </td>
                    <td style="width:20%;">
                        <img src="{{url('/images/invoice-status/'.$invoice_status.'.png')}}">
                    </td>
                    <td style="width:40%; vertical-align: top;">
                        <p style="font-size:14px;text-align:right;"><span style="font-size:16px; font-weight: bold;">{{$invoice->Customer->name}}</span><br />
                        {!! trans('user.email').' : '.$invoice->Customer->email.' <br />'!!}
                        {!! ($invoice->Customer->Profile->phone) ? (trans('user.phone').' : '.$invoice->Customer->Profile->phone.' <br />') : ''!!}
                        {!! ($invoice->Customer->Profile->address_line_1) ? ($invoice->Customer->Profile->address_line_1.' <br />') : ''!!}
                        {!! ($invoice->Customer->Profile->address_line_2) ? ($invoice->Customer->Profile->address_line_2.' <br />') : ''!!}
                        {!! ($invoice->Customer->Profile->city) ? ($invoice->Customer->Profile->city.' <br />') : ''!!}
                        {!! ($invoice->Customer->Profile->state) ? ($invoice->Customer->Profile->state.' <br />') : ''!!}
                        {!! ($invoice->Customer->Profile->zipcode) ? ($invoice->Customer->Profile->zipcode.' <br />') : ''!!}
                        {!! ($invoice->Customer->Profile->country_id) ? config('country.'.$invoice->Customer->Profile->country_id) : ''!!}
                        </p>
                    </td>
                </tr>
            </table>
            <div style="margin:5px 0px;">&nbsp;</div>
            <table class="fancy-detail">
                <thead>
                    <tr>
                        <th> # </th>
                        <th> {{trans('invoice.item')}} </th>
                        @if($invoice->line_item_description)
                            <th> {{trans('invoice.description')}} </th>
                        @endif
                        @if($invoice->item_type != 'amount')
                            <th style="text-align: right;">
                                @if($invoice->item_type == 'quantity')
                                    {{trans('invoice.quantity')}}
                                @else
                                    {{trans('invoice.hour')}}
                                @endif
                            </th>
                        @endif
                        <th style="text-align: right;"> {{trans('invoice.unit_price')}} </th>
                        @if($invoice->line_item_discount)
                        <th style="text-align: right;"> {{trans('invoice.discount')}} </th>
                        @endif
                        @if($invoice->line_item_tax)
                        <th style="text-align: right;"> {{trans('invoice.tax')}} </th>
                        @endif
                        <th style="width:150px;text-align: right;"> {{trans('invoice.amount')}} </th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    @foreach($invoice->InvoiceItem as $invoice_item)
                    <tr>
                        <td> {{$i}} </td>
                        <td> {{($invoice_item->Item) ? $invoice_item->Item->name : $invoice_item->name}} </td>
                        @if($invoice->line_item_description)
                            <td> {{$invoice_item->description}} </td>
                        @endif
                        @if($invoice->item_type != 'amount')
                            <td style="text-align: right;"> {{round($invoice_item->quantity,config('config.invoice_line_item_quantity_decimal_place'))}} </td>
                        @endif
                        <td style="text-align: right;"> {{currency($invoice_item->unit_price,$invoice->Currency,1)}} </td>
                        @if($invoice->line_item_discount)
                            <td style="text-align: right;"> {{
                            (!$invoice->line_item_discount_type) ? currency($invoice_item->item_discount,$invoice->Currency,1) : (round($invoice_item->discount,config('config.invoice_line_item_discount_decimal_place')).' %')
                            }} </td>
                        @endif
                        @if($invoice->line_item_tax)
                            <td style="text-align: right;"> {{round($invoice_item->tax,config('config.invoice_line_item_tax_decimal_place')).' %'}} </td>
                        @endif
                        <td style="text-align: right;"> {{currency($invoice_item->amount,$invoice->Currency,1)}} </td>
                    </tr>
                    <?php $i++; ?>
                    @endforeach
                </tbody>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
                <tr>
                    <td style="width:60%;vertical-align: top;">
                        @if($invoice->tnc)
                            <h2>{{trans('invoice.tnc')}}</h2>
                            <p style="font-size: 13px;">{{$invoice->tnc}}</p>
                        @endif
                        @if($invoice->customer_note)
                            <h2>{{trans('invoice.customer_note')}}</h2>
                            <p style="font-size: 13px;">{{$invoice->customer_note}}</p>
                        @endif
                    </td>
                    <td style="width:40%;vertical-align: top;">
                        <table class="fancy-detail" style="width:100%;">
                            @if($invoice->subtotal_discount && $invoice->subtotal_discount_amount > 0)
                            <tr>
                                <td>{{trans('invoice.subtotal_discount')}}</td>
                                <td style="width:150px; text-align: right;">
                                    {{
                                        (!$invoice->subtotal_discount_type) ? currency($invoice->subtotal_discount_amount,$invoice->Currency,1) : (round($invoice->subtotal_discount_amount,config('config.invoice_subtotal_discount_decimal_place')).' %')
                                    }}
                                </td>
                            </tr>
                            @endif
                            @if($invoice->subtotal_tax && $invoice->subtotal_tax_amount > 0)
                            <tr>
                                <td>{{trans('invoice.subtotal_tax')}}</td>
                                <td style="width:150px; text-align: right;">{{currency($invoice->subtotal_tax_amount,$invoice->Currency,1)}}</td>
                            </tr>
                            @endif
                            @if($invoice->subtotal_shipping_and_handling && $invoice->subtotal_shipping_and_handling_amount > 0)
                            <tr>
                                <td>{{trans('invoice.subtotal_shipping_and_handling')}}</td>
                                <td style="width:150px; text-align: right;">{{currency($invoice->subtotal_shipping_and_handling_amount,$invoice->Currency,1)}}</td>
                            </tr>
                            @endif
                            <tr>
                                <td style="font-size:16px; font-weight: bold;">{{trans('invoice.total')}}</td>
                                <td style="width:150px; text-align: right;font-size:16px; font-weight: bold;">{{currency($invoice->total,$invoice->Currency,1)}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

