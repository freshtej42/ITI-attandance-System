<?php

namespace App\Console\Commands;

use App\Mail\DailyQrCodeMail;
use App\Services\EmailService;
use App\Services\QrCodeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateDailyQrCode extends Command
{
    protected $signature = 'attendance:generate-daily-qrcode';
    protected $description = 'Generate daily QR code for attendance and email it to admin';

    protected QrCodeService $qrCodeService;
    protected EmailService $emailService;

    public function __construct(QrCodeService $qrCodeService, EmailService $emailService)
    {
        parent::__construct();

        $this->qrCodeService = $qrCodeService;
        $this->emailService = $emailService;
    }

    public function handle(): int
    {
        $this->info('Starting daily QR code generation...');

        $date = now()->toDateString();

        // Generate QR code image content (PNG)
        $image = $this->qrCodeService->generateDailyQrCodeImage($date);

        // File path to store
        $fileName = "daily_qrcodes/qr_{$date}.png";

        Storage::disk('public')->put($fileName, $image);

        $this->info("QR code saved to storage/app/public/{$fileName}");

        // Retrieve admin emails
        $adminEmails = config('attendance.admin_emails', []);

        if (empty($adminEmails)) {
            $this->error('No admin emails configured in config/attendance.php');
            return self::FAILURE;
        }

        foreach ($adminEmails as $adminEmail) {
            $this->info("Sending QR code email to {$adminEmail}...");

            $mail = new DailyQrCodeMail($date, $fileName);

            try {
                $this->emailService->send($adminEmail, $mail);
            } catch (\Exception $ex) {
                $this->error("Failed to send email to {$adminEmail}: " . $ex->getMessage());
                return self::FAILURE;
            }
        }

        $this->info('Daily QR code generation and email sending completed.');

        return self::SUCCESS;
    }
}
