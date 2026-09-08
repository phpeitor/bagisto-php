@component('shop::emails.layout')
    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 24px;">
        @lang('shop::app.emails.complaint-book.greeting', ['name' => $complaint->first_name])
    </p>

    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 24px;">
        @lang('shop::app.emails.complaint-book.resolved-intro', ['correlative' => $complaint->correlative])
    </p>

    @if ($complaint->admin_notes)
        <div style="margin-bottom: 24px; padding: 20px; border: 1px solid #E3E9E5; border-radius: 8px;">
            <p style="font-size: 13px;text-transform: uppercase;letter-spacing: .04em;color: #9AA0AC;margin: 0 0 12px;">
                @lang('shop::app.emails.complaint-book.resolved-response')
            </p>

            <p style="font-size: 14px;color: #384860;line-height: 20px;margin: 0;">
                {{ $complaint->admin_notes }}
            </p>
        </div>
    @endif

    <p style="font-size: 16px;color: #384860;line-height: 24px;margin-bottom: 40px;">
        @lang('shop::app.emails.complaint-book.resolved-footer')
    </p>
@endcomponent
