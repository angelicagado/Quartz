<?php

namespace App\Services;

use App\Models\EventParticipant;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class QrCodeService
{
    /**
     * Ensure the participant has a unique attendance token.
     *
     * The token is the single source of truth for the participant's QR code;
     * the image itself is rendered on demand from this token, never stored.
     * Called on both self-registration and admin-side adds.
     */
    public function ensureTokenFor(EventParticipant $participant): void
    {
        if ($participant->qr_token !== null) {
            return;
        }

        $participant->update(['qr_token' => Str::random(40)]);
    }

    /**
     * Render the participant's QR code as an SVG string.
     *
     * The QR encodes the attendance scan URL with the participant's unique
     * token so organizers can check them in.
     */
    public function svgFor(EventParticipant $participant): string
    {
        $scanUrl = route('attendance.scan', ['event' => $participant->event_id])
            .'?token='.$participant->qr_token;

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($scanUrl);
    }
}
