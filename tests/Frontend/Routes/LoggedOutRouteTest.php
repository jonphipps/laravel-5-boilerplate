<?php

namespace Tests\Frontend\Routes;


use Tests\TestCase;
use App\Models\Access\User\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Event;
use App\Events\Frontend\Auth\UserConfirmed;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Frontend\Auth\UserNeedsConfirmation;

/**
 * Class LoggedOutRouteTest.
 */
class LoggedOutRouteTest extends TestCase
{
    /**
     * User Logged Out Frontend.
     */

    /**
     * Test the homepage works.
     */
    public function testHomePage()
    {
        $response = $this->get('/')->assertResponseOk();
    }

    /**
     * Test the macro page works.
     */
    public function testMacroPage()
    {
        $response = $this->get('/macros')->see('Macro Examples');
    }

    /**
     * Test the login page works.
     */
    public function testLoginPage()
    {
        $response = $this->get('/login')->see('Login');
    }

    /**
     * Test the register page works.
     */
    public function testRegisterPage()
    {
        $response = $this->get('/register')->see('Register');
    }

    /**
     * Test the forgot password page works.
     */
    public function testForgotPasswordPage()
    {
        $response = $this->get('password/reset')->see('Reset Password');
    }

    /**
     * Test the dashboard page redirects to login.
     */
    public function testDashboardPageLoggedOut()
    {
        $response = $this->get('/dashboard')->seePageIs('/login');
    }

    /**
     * Test the account page redirects to login.
     */
    public function testAccountPageLoggedOut()
    {
        $response = $this->get('/account')->seePageIs('/login');
    }

    /**
     * Create an unconfirmed user and assure the user gets
     * confirmed when hitting the confirmation route.
     */
    public function testConfirmAccountRoute()
    {
        Event::fake();

        // Create default user to test with
        $unconfirmed = factory(User::class)->states('unconfirmed')->create();
        $unconfirmed->attachRole(3); //User

        $response = $this->get('/account/confirm/'.$unconfirmed->confirmation_code)
             ->seePageIs('/login')
             ->see('Your account has been successfully confirmed!')
             ->assertDatabaseHas(config('access.users_table'), ['email' => $unconfirmed->email, 'confirmed' => 1]);

        Event::assertDispatched(UserConfirmed::class);
    }

    /**
     * Assure the user gets resent a confirmation email
     * after hitting the resend confirmation route.
     */
    public function testResendConfirmAccountRoute()
    {
        Notification::fake();

        $response = $this->get('/account/confirm/resend/'.$this->user->id)
             ->seePageIs('/login')
             ->see('A new confirmation e-mail has been sent to the address on file.');

        Notification::assertSentTo(
            [$this->user],
            UserNeedsConfirmation::class
        );
    }

    /**
     * Test the language switcher changes the desired language in the session.
     */
    public function testLanguageSwitcher()
    {
        $response = $this->get('lang/es')->see('Registrarse')->assertSessionHas('locale', 'es');

        App::setLocale('en');
    }

    /**
     * Test the generic 404 page.
     */
    public function test404Page()
    {
        $this->get('7g48hwbfw9eufj')->seeStatusCode(404)->see('Page Not Found');
    }
}
