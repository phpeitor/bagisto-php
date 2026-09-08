@component('shop::emails.layout')
    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 24px;">
        @lang('shop::app.emails.complaint-book.greeting', ['name' => $complaint->first_name])
    </p>

    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 24px;">
        {!! trans('shop::app.emails.complaint-book.confirmation', ['correlative' => $complaint->correlative]) !!}
    </p>

    @php
        $row = 'font-size: 14px;color: #6B6F76;line-height: 20px;margin: 0 0 10px;';
        $strong = 'color: #384860;';
    @endphp

    <!-- Consumer -->
    <div style="margin-bottom: 16px; padding: 20px; border: 1px solid #E3E9E5; border-radius: 8px;">
        <p style="font-size: 13px;text-transform: uppercase;letter-spacing: .04em;color: #9AA0AC;margin: 0 0 12px;">
            @lang('shop::app.complaint-book.create.consumer-section')
        </p>

        <p style="{{ $row }}">
            @lang('shop::app.complaint-book.create.last-name') / @lang('shop::app.complaint-book.create.first-name'):
            <strong style="{{ $strong }}">{{ $complaint->last_name }}, {{ $complaint->first_name }}</strong>
        </p>

        <p style="{{ $row }}">
            @lang('shop::app.complaint-book.create.document-type'):
            <strong style="{{ $strong }}">{{ strtoupper($complaint->document_type) }} {{ $complaint->document_number }}</strong>
        </p>

        <p style="{{ $row }}">
            @lang('shop::app.complaint-book.create.address'):
            <strong style="{{ $strong }}">{{ $complaint->address }}</strong>
        </p>

        @if ($complaint->phone)
            <p style="{{ $row }}">
                @lang('shop::app.complaint-book.create.phone'):
                <strong style="{{ $strong }}">{{ $complaint->phone }}</strong>
            </p>
        @endif

        <p style="{{ $row }} margin-bottom: 0;">
            @lang('shop::app.complaint-book.create.email'):
            <strong style="{{ $strong }}">{{ $complaint->email }}</strong>
        </p>
    </div>

    <!-- Good or service -->
    <div style="margin-bottom: 16px; padding: 20px; border: 1px solid #E3E9E5; border-radius: 8px;">
        <p style="font-size: 13px;text-transform: uppercase;letter-spacing: .04em;color: #9AA0AC;margin: 0 0 12px;">
            @lang('shop::app.complaint-book.create.good-section')
        </p>

        <p style="{{ $row }}">
            @lang('shop::app.complaint-book.create.good-type'):
            <strong style="{{ $strong }}">
                {{ $complaint->good_type === 'producto'
                    ? trans('shop::app.complaint-book.create.good-type-product')
                    : trans('shop::app.complaint-book.create.good-type-service') }}
            </strong>
        </p>

        <p style="{{ $row }}">
            @lang('shop::app.complaint-book.create.good-description'):
            <strong style="{{ $strong }}">{{ $complaint->good_description }}</strong>
        </p>

        <p style="{{ $row }} margin-bottom: 0;">
            @lang('shop::app.complaint-book.create.claimed-amount'):
            <strong style="{{ $strong }}">{{ $complaint->claimed_amount !== null ? 'S/ '.number_format($complaint->claimed_amount, 2) : '—' }}</strong>
        </p>
    </div>

    <!-- Claim detail -->
    <div style="margin-bottom: 24px; padding: 20px; border: 1px solid #E3E9E5; border-radius: 8px;">
        <p style="font-size: 13px;text-transform: uppercase;letter-spacing: .04em;color: #9AA0AC;margin: 0 0 12px;">
            @lang('shop::app.complaint-book.create.claim-section')
        </p>

        <p style="{{ $row }}">
            @lang('shop::app.complaint-book.create.type'):
            <strong style="{{ $strong }}">
                {{ $complaint->type === 'reclamo'
                    ? trans('shop::app.complaint-book.create.type-claim')
                    : trans('shop::app.complaint-book.create.type-complaint') }}
            </strong>
        </p>

        <p style="{{ $row }}">
            @lang('shop::app.complaint-book.create.detail'):
            <strong style="{{ $strong }}">{{ $complaint->detail }}</strong>
        </p>

        <p style="{{ $row }} margin-bottom: 0;">
            @lang('shop::app.complaint-book.create.request'):
            <strong style="{{ $strong }}">{{ $complaint->request }}</strong>
        </p>
    </div>

    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 40px;">
        @lang('shop::app.emails.complaint-book.response-time')
    </p>
@endcomponent
