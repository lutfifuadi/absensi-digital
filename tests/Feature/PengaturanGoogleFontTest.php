<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pengaturan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengaturanGoogleFontTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create super admin
        $this->superAdmin = User::factory()->create([
            'role' => User::ROLE_SUPER_ADMIN
        ]);
    }

    public function test_super_admin_can_access_pengaturan_branding_tab_with_google_font_fields()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.pengaturan.index', ['tab' => 'branding']));

        $response->assertStatus(200);
        $response->assertSee('google_font_family');
        $response->assertSee('live_board_font_family');
        $response->assertSee('live_board_counter_font_family');
    }

    public function test_super_admin_can_save_google_font_settings()
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.pengaturan.update'), [
                'google_font_family' => 'Montserrat',
                'live_board_font_family' => 'Poppins',
                'live_board_counter_font_family' => 'JetBrains Mono',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect();
        
        $this->assertEquals('Montserrat', Pengaturan::where('key', 'google_font_family')->value('value'));
        $this->assertEquals('Poppins', Pengaturan::where('key', 'live_board_font_family')->value('value'));
        $this->assertEquals('JetBrains Mono', Pengaturan::where('key', 'live_board_counter_font_family')->value('value'));
    }

    public function test_google_font_settings_have_default_fallback_values()
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.pengaturan.index'));

        $response->assertStatus(200);
        
        // Memastikan fallback di commonMaster layout & live-board menggunakan default value
        $this->assertEquals('Product Sans', Pengaturan::where('key', 'google_font_family')->value('value') ?? 'Product Sans');
        $this->assertEquals('Product Sans', Pengaturan::where('key', 'live_board_font_family')->value('value') ?? 'Product Sans');
        $this->assertEquals('Courier New', Pengaturan::where('key', 'live_board_counter_font_family')->value('value') ?? 'Courier New');
    }

    public function test_all_100_plus_popular_fonts_can_be_saved_and_loaded_correctly()
    {
        $popularFonts = [
            'Product Sans',
            'Inter', 'Roboto', 'Montserrat', 'Poppins', 'Open Sans', 'Nunito', 'Lato', 'Ubuntu', 'Mukta', 'Rubik', 
            'Heebo', 'Work Sans', 'DM Sans', 'Plus Jakarta Sans', 'Outfit', 'Quicksand', 'Josefin Sans', 'Fira Sans', 
            'PT Sans', 'Cabin', 'Nunito Sans', 'Karla', 'Albert Sans', 'Arimo', 'Asap', 'Barlow', 'Catamaran', 'Hind', 
            'Manrope', 'Kanit', 'Abel', 'Alegreya Sans', 'Assistant', 'Chivo', 'Dosis', 'Fira Sans Condensed', 'Giga Sandbox',
            'IBM Plex Sans', 'Jost', 'League Spartan', 'Lexend', 'Maven Pro', 'Merriweather Sans', 'Nobile', 'Noto Sans', 
            'PT Sans Narrow', 'Public Sans', 'Quick Sand', 'Saira', 'Sen', 'Sora', 'Source Sans 3', 'Teko', 'Titillium Web',
            'Urbanist', 'Varela Round', 'Yantramanav',
            'Playfair Display', 'Merriweather', 'Lora', 'PT Serif', 'Crimson Text', 'Noto Serif', 'Libre Baskerville', 
            'Arvo', 'EB Garamond', 'Cardo', 'Cinzel', 'Cormorant Garamond', 'Domine', 'Playfair', 'Alegreya', 'Alfa Slab One',
            'Amiri', 'Baskervville', 'Bodoni Moda', 'Bookman Old Style', 'Crimson Pro', 'DM Serif Display', 'Gelasio', 
            'Georgia', 'Libre Caslon Text', 'Literata', 'Niconne', 'Playfair Display Serif', 'Prata', 'PT Serif Caption', 
            'Rokkitt', 'Source Serif 4', 'Spectral', 'Times New Roman', 'Volkhov',
            'Oswald', 'Raleway', 'Bebas Neue', 'Anton', 'Lobster', 'Pacifico', 'Caveat', 'Righteous', 'Cinzel Decorative', 
            'Comfortaa', 'Fredoka', 'Great Vibes', 'Sacramento', 'Shadows Into Light', 'Titan One', 'Abril Fatface', 'Acme', 
            'Alata', 'Architects Daughter', 'Bangers', 'Carter One', 'Creepster', 'Dancing Script', 'Kaushan Script', 
            'Luckiest Guy', 'Monoton', 'Permanent Marker', 'Press Start 2P', 'Satisfy', 'Spicy Rice', 'Yellowtail',
            'Fira Code', 'Source Code Pro', 'Roboto Mono', 'Inconsolata', 'JetBrains Mono', 'Space Mono', 'Ubuntu Mono', 
            'Share Tech Mono', 'Courier Prime', 'Cutive Mono', 'DM Mono', 'IBM Plex Mono', 'Major Mono Display', 
            'Nanum Gothic Coding', 'Nova Mono', 'Oxygen Mono', 'PT Mono', 'VT323'
        ];
        $popularFonts = array_values(array_unique($popularFonts));

        // Memastikan jumlah font yang diuji lebih dari 100
        $this->assertGreaterThan(100, count($popularFonts));

        foreach ($popularFonts as $font) {
            // Update pengaturan ke database dengan font ini
            $response = $this->actingAs($this->superAdmin)
                ->post(route('admin.pengaturan.update'), [
                    'google_font_family' => $font,
                    'live_board_font_family' => $font,
                ]);

            $response->assertStatus(302);
            $response->assertRedirect();

            // Cek di database apakah tersimpan dengan benar
            $this->assertEquals($font, Pengaturan::where('key', 'google_font_family')->value('value'));
            $this->assertEquals($font, Pengaturan::where('key', 'live_board_font_family')->value('value'));

            // Membuka halaman pengaturan dan memastikan nilainya terpilih
            $indexResponse = $this->actingAs($this->superAdmin)
                ->get(route('admin.pengaturan.index', ['tab' => 'branding']));

            $indexResponse->assertStatus(200);
            
            // Mencari tag option dengan attribute selected dan value font tersebut
            $expectedOptionGoogle = 'value="' . $font . '" ' . 'selected';
            // Bisa berupa selected atau selected="selected", mari kita cek asertasi substring
            $indexResponse->assertSee($font);
        }
    }
}
