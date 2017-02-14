<?php

namespace Tests\Backend\Access\User;

use Tests\TestCase;

/**
 * Class UserAccessTest.
 */
class UserAccessTest extends TestCase
{
    public function testUserCantAccessAdminDashboard()
    {
        $this->actingAs($this->user);
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/');
        $response = $this->get('/');
        $response ->assertSee('You do not have access to do that.');
    }

    public function testExecutiveCanAccessAdminDashboard()
    {
        $response = $this->get('/')
                         ->actingAs($this->executive)
                         ->visit('/admin/dashboard')
                         ->seePageIs('/admin/dashboard')
                         ->see($this->executive->name);
    }

    public function testExecutiveCantAccessManageRoles()
    {
        $response = $this->get('/')
                         ->actingAs($this->executive)
                         ->visit('/admin/dashboard')
                         ->seePageIs('/admin/dashboard')
                         ->visit('/admin/access/role')
                         ->seePageIs('/')
                         ->see('You do not have access to do that.');
    }
}
