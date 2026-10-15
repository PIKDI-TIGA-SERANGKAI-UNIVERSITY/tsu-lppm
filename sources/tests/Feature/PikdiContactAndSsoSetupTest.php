<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Kontak Helpdesk PIKDI di landing berasal dari tsu-core (bukan mailto yang ditulis langsung), dan
 * teks contoh SSO di .env.example dikenali tsu-foundation sebagai "belum dikonfigurasi".
 */
class PikdiContactAndSsoSetupTest extends TestCase
{
    protected array $requiresPackages = ['foundation'];

    public function test_landing_uses_the_central_helpdesk_contact(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('https://wa.me/6285111415175?text=', $html);
        $this->assertStringContainsString('Helpdesk PIKDI +62 851-1141-5175', $html);
        $this->assertStringContainsString('mailto:pikdi@tsu.ac.id', $html);
    }

    public function test_env_example_sso_placeholders_are_reported_as_not_configured(): void
    {
        $example = [];
        foreach (file(base_path('.env.example'), FILE_IGNORE_NEW_LINES) as $line) {
            if (preg_match('/^(TSU_SSO_[A-Z_]+|HOMEBASE_CLIENT_[A-Z_]+)=("?)([^"#]*)\2/', $line, $m)) {
                $example[$m[1]] = trim($m[3]);
            }
        }

        config([
            'tsu-foundation.sso.client_id' => $example['TSU_SSO_CLIENT_ID'],
            'tsu-foundation.sso.client_secret' => $example['TSU_SSO_CLIENT_SECRET'],
            'tsu-foundation.sso.redirect_uri' => $example['TSU_SSO_REDIRECT_URI'],
            'tsu-foundation.homebase.client_id' => $example['HOMEBASE_CLIENT_ID'],
            'tsu-foundation.homebase.client_secret' => $example['HOMEBASE_CLIENT_SECRET'],
        ]);

        $sso = \Pikdi\TsuFoundation\Support\SsoConfiguration::class;
        $this->assertSame(['TSU_SSO_CLIENT_ID', 'TSU_SSO_CLIENT_SECRET', 'TSU_SSO_REDIRECT_URI'], array_keys($sso::problems()));
        $this->assertSame(['HOMEBASE_CLIENT_ID', 'HOMEBASE_CLIENT_SECRET'], array_keys($sso::clientCredentialProblems()));

        // Tombol login SSO tidak mengarah ke Homebase selama .env masih teks contoh
        $this->get('/login/sso')->assertStatus(503)->assertSee('Login SSO Belum Dikonfigurasi');
    }
}
