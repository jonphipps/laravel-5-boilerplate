<?php

namespace Tests\Frontend\Routes;

use Illuminate\Support\Facades\Event;
use App\Events\Frontend\Auth\UserLoggedOut;
use Tests\BrowserKitTest;

/**
 * Class LoggedInRouteTest.
 */
class LoggedInRouteTest extends BrowserKitTest
{
    /**
     * Test the homepage works and the dashboard button appears.
     */
    public function testHomePageLoggedIn()
    {
        $this->actingAs($this->user)
            ->visit('/')
            ->see('Dashboard')
            ->see($this->user->name)
            ->dontSee('Administration');
    }

    /**
     * Test the dashboard page works and displays the users information.
     */
    public function testDashboardPage()
    {
        $this->actingAs($this->user)
            ->visit('/dashboard')
            ->see($this->user->email)
            ->see('Joined')
            ->dontSee('Administration');
    }

    /**
     * Test the account page works and displays the users information.
     */
    public function testAccountPage()
    {
        $this->actingAs($this->user)
            ->visit('/account')
            ->see('My Account')
            ->see('Profile')
            ->see('Update Information')
            ->see('Change Password')
            ->dontSee('Administration');
    }

    /**
     * Test the account page works and displays the users information.
     */
    public function testLoggedInAdmin()
    {
        $this->actingAs($this->admin)
            ->visit('/')
            ->see('Administration')
            ->see($this->admin->name);
    }

    /**
     * Test the logout button redirects the user back to home and the login button is again visible.
     */
    public function testLogoutRoute()
    {
        // Make sure our events are fired
        Event::fake();

        $this->actingAs($this->user)
            ->visit('/logout')
            ->see('Login')
            ->see('Register');

        Event::assertDispatched(UserLoggedOut::class);
    }
}
