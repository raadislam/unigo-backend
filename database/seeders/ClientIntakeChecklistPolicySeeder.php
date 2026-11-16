<?php

namespace Database\Seeders;

use App\Models\VisaRequirement;
use Illuminate\Database\Seeder;

class ClientIntakeChecklistPolicySeeder extends Seeder
{
    public function run(): void
    {
        VisaRequirement::query()
            ->where('passport_nationality_iso3', 'BGD')
            ->where('travel_purpose', 'tourism')
            ->where('verification_status', 'verified')
            ->where('is_published', true)
            ->chunkById(100, function ($requirements): void {
                foreach ($requirements as $requirement) {
                    $profiles = $requirement->applicant_checklists ?? [];
                    $changed = false;

                    foreach ($profiles as &$profile) {
                        if (! is_array($profile)) {
                            continue;
                        }

                        if (! array_key_exists('client_documents', $profile)) {
                            $profile['client_documents'] = [];
                            $profile['checklist_purpose'] = 'documents_client_must_submit_to_unigo';
                            $profile['checklist_review_status'] = 'awaiting_country_specific_classification';
                            $profile['checklist_review_note'] = 'This country profile has not yet been classified into client-submitted and UniGo-managed documents. Do not issue a client intake PDF until reviewed.';
                            $changed = true;
                        }
                    }
                    unset($profile);

                    if ($changed) {
                        $requirement->forceFill(['applicant_checklists' => $profiles])->save();
                    }
                }
            });
    }
}
