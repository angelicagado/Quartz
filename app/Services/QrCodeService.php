<?php

namespace App\Services;

use App\Models\EventParticipant;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrCodeService
{
    /**
     * Generate a QR code SVG for the given participant and store it on the public disk.
     *
     * The QR encodes the attendance scan URL with a unique token so organizers can
     * check the participant in. Called on both self-registration and admin-side adds.
     */
    public function generateFor(EventParticipant $participant): void
    {
        $token = Str::random(40);

        $participant->update(['qr_token' => $token]);

        $scanUrl = route('attendance.scan', ['event' => $participant->event_id])
            .'?token='.$token;

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);
        $svgContent = $writer->writeString($scanUrl);

        $relativePath = 'qrcodes/event_'.$participant->event_id.'_participant_'.$participant->id.'.svg';

        Storage::disk('public')->put($relativePath, $svgContent);

        $participant->update(['qr_code_path' => 'public/'.$relativePath]);
    }
}
