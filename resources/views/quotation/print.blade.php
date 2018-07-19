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
                        @if($quotation->reference_number)
                        <tr>
                            <th>{{trans('quotation.reference_number')}}</th><td>{{$quotation->reference_number}}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>{{trans('quotation.quotation_number')}}</th><td>{{$quotation->quotation_number}}</td>
                        </tr>
                        <tr>
                            <th>{{trans('quotation.date')}}</th><td>{{showDate($quotation->date)}}</td>
                        </tr>
                        @if($quotation->expiry_date != 'no_expiry_date')
                        <tr>
                            <th>{{trans('quotation.expiry_date')}}</th><td>{{showDate($quotation->expiry_date_detail)}}</td>
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
                    <td style="width:20%">
                        <img src="{{url('/images/quotation-status/'.$quotation_status.'.png')}}">
                    </td>
                    <td style="width:20%; vertical-align: top;">
                        <p style="font-size:14px;text-align:right;"><span style="font-size:16px; font-weight: bold;">{{$quotation->Customer->name}}</span><br />
                        {!! trans('user.email').' : '.$quotation->Customer->email.' <br />'!!}
                        {!! ($quotation->Customer->Profile->phone) ? (trans('user.phone').' : '.$quotation->Customer->Profile->phone.' <br />') : ''!!}
                        {!! ($quotation->Customer->Profile->address_line_1) ? ($quotation->Customer->Profile->address_line_1.' <br />') : ''!!}
                        {!! ($quotation->Customer->Profile->address_line_2) ? ($quotation->Customer->Profile->address_line_2.' <br />') : ''!!}
                        {!! ($quotation->Customer->Profile->city) ? ($quotation->Customer->Profile->city.' <br />') : ''!!}
                        {!! ($quotation->Customer->Profile->state) ? ($quotation->Customer->Profile->state.' <br />') : ''!!}
                        {!! ($quotation->Customer->Profile->zipcode) ? ($quotation->Customer->Profile->zipcode.' <br />') : ''!!}
                        {!! ($quotation->Customer->Profile->country_id) ? config('country.'.$quotation->Customer->Profile->country_id) : ''!!}
                        </p>
                    </td>
                </tr>
            </table>
            <table border="0" style="width:100%;margin-top:20px;">
                <tr>
                    <td style="font-weight: bold;">{{$quotation->subject}}</td>
                </tr>
                <tr>
                    <td>{!! $quotation->description !!}</td>
                </tr>
            </table>
            <div style="margin:5px 0px;">&nbsp;</div>
            <table class="fancy-detail">
                <thead>
                    <tr>
                        <th> # </th>
                        <th> {{trans('quotation.item')}} </th>
                        @if($quotation->line_item_description)
                            <th> {{trans('quotation.description')}} </th>
                        @endif
                        @if($quotation->item_type != 'amount')
                            <th style="text-align: right;">
                                @if($quotation->item_type == 'quantity')
                                    {{trans('quotation.quantity')}}
                                @else
                                    {{trans('quotation.hour')}}
                                @endif
                            </th>
                        @endif
                        <th style="text-align: right;"> {{trans('quotation.unit_price')}} </th>
                        @if($quotation->line_item_discount)
                        <th style="text-align: right;"> {{trans('quotation.discount')}} </th>
                        @endif
                        @if($quotation->line_item_tax)
                        <th style="text-align: right;"> {{trans('quotation.tax')}} </th>
                        @endif
                        <th style="width:150px;text-align: right;"> {{trans('quotation.amount')}} </th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    @foreach($quotation->QuotationItem as $quotation_item)
                    <tr>
                        <td> {{$i}} </td>
                        <td> {{($quotation_item->Item) ? $quotation_item->Item->name : $quotation_item->name}} </td>
                        @if($quotation->line_item_description)
                            <td> {{$quotation_item->description}} </td>
                        @endif
                        @if($quotation->item_type != 'amount')
                            <td style="text-align: right;"> {{round($quotation_item->quantity,config('config.quotation_line_item_quantity_decimal_place'))}} </td>
                        @endif
                        <td style="text-align: right;"> {{currency($quotation_item->unit_price,$quotation->Currency)}} </td>
                        @if($quotation->line_item_discount)
                            <td style="text-align: right;"> {{
                            (!$quotation->line_item_discount_type) ? currency($quotation_item->item_discount,$quotation->Currency) : (round($quotation_item->discount,config('config.quotation_line_item_discount_decimal_place')).' %')
                            }} </td>
                        @endif
                        @if($quotation->line_item_tax)
                            <td style="text-align: right;"> {{round($quotation_item->tax,config('config.quotation_line_item_tax_decimal_place')).' %'}} </td>
                        @endif
                        <td style="text-align: right;"> {{currency($quotation_item->amount,$quotation->Currency)}} </td>
                    </tr>
                    <?php $i++; ?>
                    @endforeach
                </tbody>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" style="width: 100%;">
                <tr>
                    <td style="width:60%;vertical-align: top;">
                        @if($quotation->tnc)
                            <h2>{{trans('quotation.tnc')}}</h2>
                            <p style="font-size: 13px;">{{$quotation->tnc}}</p>
                        @endif
                        @if($quotation->customer_note)
                            <h2>{{trans('quotation.customer_note')}}</h2>
                            <p style="font-size: 13px;">{{$quotation->customer_note}}</p>
                        @endif
                    </td>
                    <td style="width:40%;vertical-align: top;">
                        <table class="fancy-detail" style="width:100%;">
                            @if($quotation->subtotal_discount && $quotation->subtotal_discount_amount > 0)
                            <tr>
                                <td>{{trans('quotation.subtotal_discount')}}</td>
                                <td style="width:150px; text-align: right;">
                                    {{
                                        (!$quotation->subtotal_discount_type) ? currency($quotation->subtotal_discount_amount,$quotation->Currency) : (round($quotation->subtotal_discount_amount,config('config.quotation_subtotal_discount_decimal_place')).' %')
                                    }}
                                </td>
                            </tr>
                            @endif
                            @if($quotation->subtotal_tax && $quotation->subtotal_tax_amount > 0)
                            <tr>
                                <td>{{trans('quotation.subtotal_tax')}}</td>
                                <td style="width:150px; text-align: right;">{{currency($quotation->subtotal_tax_amount,$quotation->Currency)}}</td>
                            </tr>
                            @endif
                            @if($quotation->subtotal_shipping_and_handling && $quotation->subtotal_shipping_and_handling_amount > 0)
                            <tr>
                                <td>{{trans('quotation.subtotal_shipping_and_handling')}}</td>
                                <td style="width:150px; text-align: right;">{{currency($quotation->subtotal_shipping_and_handling_amount,$quotation->Currency)}}</td>
                            </tr>
                            @endif
                            <tr>
                                <td style="font-size:16px; font-weight: bold;">{{trans('quotation.total')}}</td>
                                <td style="width:150px; text-align: right;font-size:16px; font-weight: bold;">{{currency($quotation->total,$quotation->Currency)}}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

