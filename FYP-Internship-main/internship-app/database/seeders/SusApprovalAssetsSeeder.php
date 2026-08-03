<?php

namespace Database\Seeders;

use App\Models\Logbook;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SusApprovalAssetsSeeder extends Seeder
{
    private const SUPERVISORS = [
        'supervisor1@gmail.com' => ['Mr. Adrian Lim', 'DataCore Malaysia'],
        'supervisor2@gmail.com' => ['Ms. Priya Nair', 'SecureSoft Sdn Bhd'],
        'supervisor3@gmail.com' => ['Mr. Marcus Wong', 'LogicPulse Sdn Bhd'],
    ];

    public function run(): void
    {
        foreach (self::SUPERVISORS as $email => [$name, $company]) {
            $supervisor = User::where('email', $email)->first();
            if (! $supervisor) {
                continue;
            }

            $key = strstr($email, '@', true);
            $signaturePath = "sus/approvals/{$key}-signature.svg";
            $stampPath = "sus/approvals/{$key}-stamp.svg";
            Storage::disk('local')->put($signaturePath, $this->signatureSvg($name));
            Storage::disk('local')->put($stampPath, $this->stampSvg($company));

            Profile::updateOrCreate(['user_id' => $supervisor->id], [
                'full_name' => $name,
                'company_name' => $company,
                'signature_path' => $signaturePath,
                'stamp_path' => $stampPath,
            ]);

            Logbook::where('approved_by_id', $supervisor->id)
                ->where('status', 'approved')
                ->update([
                    'approval_signature_path' => $signaturePath,
                    'approval_stamp_path' => $stampPath,
                    'approval_company_name' => $company,
                ]);
        }
    }

    private function signatureSvg(string $name): string
    {
        $safeName = htmlspecialchars($name, ENT_XML1);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="360" height="120" viewBox="0 0 360 120">
          <rect width="360" height="120" fill="white"/>
          <path d="M20 82 C70 20, 92 105, 135 55 S205 95, 250 48 S305 70, 340 35" fill="none" stroke="#172554" stroke-width="3"/>
          <text x="180" y="108" text-anchor="middle" font-family="Arial" font-size="14" fill="#334155">{$safeName} - SUS signature</text>
        </svg>
        SVG;
    }

    private function stampSvg(string $company): string
    {
        $safeCompany = htmlspecialchars($company, ENT_XML1);

        return <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="180" height="180" viewBox="0 0 180 180">
          <rect width="180" height="180" fill="white"/>
          <circle cx="90" cy="90" r="70" fill="none" stroke="#1d4ed8" stroke-width="6"/>
          <circle cx="90" cy="90" r="55" fill="none" stroke="#1d4ed8" stroke-width="2"/>
          <text x="90" y="78" text-anchor="middle" font-family="Arial" font-size="12" font-weight="bold" fill="#1d4ed8">{$safeCompany}</text>
          <text x="90" y="100" text-anchor="middle" font-family="Arial" font-size="13" font-weight="bold" fill="#1d4ed8">APPROVED</text>
          <text x="90" y="120" text-anchor="middle" font-family="Arial" font-size="10" fill="#1d4ed8">SUS TEST DATA</text>
        </svg>
        SVG;
    }
}
