<?php

namespace Tests\Feature;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Library\Application\Ports\Outbound\PasswordHasher;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    private string $id;

    protected function setUp(): void
    {
        parent::setUp();
        $this->id = (string) Str::uuid();
        DB::table('library.user')->insert([
            'id' => $this->id, 'username' => 'ana', 'role' => 'librarian',
            'password_hash' => $this->app->make(PasswordHasher::class)->hash('test-login-password'),
        ]);
    }

    protected function tearDown(): void
    {
        DB::table('library.user')->where('id', $this->id)->delete();
        parent::tearDown();
    }

    public function test_valid_credentials_receive_an_eight_hour_hs256_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', ['username' => ' Ana ', 'password' => 'test-login-password']);
        $response->assertOk()->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'role'])
            ->assertJsonPath('expires_in', 28800)->assertJsonPath('role', 'librarian')->assertJsonPath('token_type', 'Bearer');
        [$header, $payload] = explode('.', $response->json('access_token'));
        $claims = json_decode(JWT::urlsafeB64Decode($payload), true);
        $this->assertSame('HS256', json_decode(JWT::urlsafeB64Decode($header), true)['alg']);
        $this->assertSame($this->id, $claims['sub']);
        $this->assertSame(28800, $claims['exp'] - $claims['iat']);
        $this->assertSame(['sub', 'role', 'iat', 'nbf', 'exp'], array_keys($claims));
        $this->assertStringNotContainsString('test-login-password', $response->getContent());
        $this->assertArrayNotHasKey('password_hash', $response->json());
        $this->withToken($response->json('access_token'))->getJson('/api/v1/books')->assertNotFound();
    }

    public function test_unknown_user_and_wrong_password_have_identical_responses(): void
    {
        $wrong = $this->postJson('/api/v1/auth/login', ['username' => 'ana', 'password' => 'wrong-password'])->assertUnauthorized();
        $unknown = $this->postJson('/api/v1/auth/login', ['username' => 'missing', 'password' => 'wrong-password'])->assertUnauthorized();
        $this->assertSame($wrong->json(), $unknown->json());
        $wrong->assertExactJson(['code' => 'INVALID_CREDENTIALS', 'message' => 'Credenciales inválidas.', 'details' => []]);
    }

    public function test_validation_has_the_contract_body_without_secret_values(): void
    {
        $response = $this->postJson('/api/v1/auth/login', ['password' => 'secret-input']);
        $response->assertUnprocessable()->assertJsonPath('code', 'VALIDATION_ERROR')->assertJsonStructure(['code', 'message', 'details']);
        $this->assertStringNotContainsString('secret-input', $response->getContent());
    }

    public function test_all_contract_operations_require_a_token(): void
    {
        $routes = [
            ['POST', '/api/v1/users'], ['GET', '/api/v1/categories'],
            ['GET', '/api/v1/books'], ['GET', '/api/v1/books/'.$this->id],
            ['POST', '/api/v1/books'], ['PUT', '/api/v1/books/'.$this->id],
            ['DELETE', '/api/v1/books/'.$this->id], ['PUT', '/api/v1/books/'.$this->id.'/cover'],
            ['DELETE', '/api/v1/books/'.$this->id.'/cover'], ['GET', '/api/v1/books/'.$this->id.'/cover'],
            ['GET', '/api/v1/members'], ['GET', '/api/v1/members/'.$this->id], ['POST', '/api/v1/members'],
            ['GET', '/api/v1/loans'], ['POST', '/api/v1/loans'], ['GET', '/api/v1/loans/'.$this->id],
            ['GET', '/api/v1/loans/overdue'], ['POST', '/api/v1/loans/'.$this->id.'/items/'.$this->id.'/return'],
            ['GET', '/api/v1/reports/most-borrowed'], ['GET', '/future-endpoint'],
        ];
        foreach ($routes as [$method, $url]) {
            $this->json($method, $url)->assertUnauthorized()->assertJsonStructure(['code', 'message', 'details']);
        }
    }

    public function test_librarian_is_forbidden_before_resource_lookup_and_admin_is_allowed_through(): void
    {
        $token = $this->postJson('/api/v1/auth/login', ['username' => 'ana', 'password' => 'test-login-password'])->json('access_token');
        foreach ([['POST', '/api/v1/users'], ['POST', '/api/v1/books'], ['PUT', '/api/v1/books/'.$this->id], ['DELETE', '/api/v1/books/'.$this->id], ['GET', '/api/v1/reports/most-borrowed']] as [$method, $url]) {
            $this->withToken($token)->json($method, $url)->assertForbidden()->assertJsonPath('code', 'FORBIDDEN_ROLE');
        }
        $admin = $this->postJson('/api/v1/auth/login', ['username' => 'admin', 'password' => 'isolated-admin-password'])->assertOk()->json('access_token');
        // Las demás HU aún no tienen controlador: admin supera autorización y obtiene 404.
        $this->withToken($admin)->postJson('/api/v1/books')->assertNotFound();
    }

    public function test_invalid_expired_tampered_and_missing_claim_tokens_are_rejected(): void
    {
        $now = time();
        $claims = ['sub' => $this->id, 'role' => 'librarian', 'iat' => $now, 'nbf' => $now, 'exp' => $now + 28800];
        $expired = array_replace($claims, ['iat' => $now - 28801, 'nbf' => $now - 28801, 'exp' => $now - 1]);
        $missing = ['sub' => $this->id, 'role' => 'librarian'];
        $tokens = [
            'not-a-token',
            JWT::encode($claims, 'another-key-at-least-32-characters', 'HS256'),
            JWT::encode($expired, config('library.jwt_secret'), 'HS256'),
            JWT::encode($missing, config('library.jwt_secret'), 'HS256'),
            JWT::encode(array_replace($claims, ['role' => 'other']), config('library.jwt_secret'), 'HS256'),
        ];
        foreach ($tokens as $token) {
            $this->withToken($token)->getJson('/api/v1/books')->assertUnauthorized();
        }
    }

    public function test_cors_allows_only_the_configured_frontend(): void
    {
        $this->withHeader('Origin', 'http://localhost:5173')->getJson('/health')
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173');
        $this->withHeader('Origin', 'https://other.invalid')->getJson('/health')
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
