<?php

namespace Tests\Backend\Forms\Access;

use App\Models\Access\Role\Role;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use App\Events\Backend\Access\Role\RoleCreated;
use App\Events\Backend\Access\Role\RoleDeleted;
use App\Events\Backend\Access\Role\RoleUpdated;
use Tests\BrowserKitTest;

/**
 * Class RoleFormTest.
 */
class RoleFormTest extends BrowserKitTest
{

    public function testCreateRoleRequiredFieldsAll()
    {
        // All Permissions
        $this->actingAs($this->admin)
            ->visit('/admin/access/role/create')
            ->type('', 'name')
            ->press('Create')
            ->seePageIs('/admin/access/role/create')
            ->see('The name field is required.');
    }

    public function testCreateRoleRequiredFieldsSpecificPermissions()
    {
        //$this->setupDatabase();
        // Custom Permissions
        $this->actingAs($this->admin)
            ->visit('/admin/access/role/create')
            ->type('Test Role', 'name')
            ->select('custom', 'associated-permissions')
            ->press('Create')
            ->seePageIs('/admin/access/role/create')
            ->see('You must select at least one permission for this role.');
    }

    public function testCreateRoleFormAll()
    {
        // Make sure our events are fired
        Event::fake();

        // Test create with all permissions
        $this->actingAs($this->admin)
            ->visit('/admin/access/role/create')
            ->type('Test Role', 'name')
            ->type('999', 'sort')
            ->press('Create')
            ->seePageIs('/admin/access/role')
            ->see('The role was successfully created.')
            ->seeInDatabase($this->roleTable, ['name' => 'Test Role', 'all' => 1, 'sort' => 999]);

        Event::assertDispatched(RoleCreated::class);
    }

    public function testCreateRoleFormSpecificPermissions()
    {
      $this->setupDatabase();
      // Make sure our events are fired
        Event::fake();

        // Test create with some permissions
        $this->actingAs($this->admin)
            ->visit('/admin/access/role/create')
            ->type('Test Role', 'name')
            ->select('custom', 'associated-permissions')
            ->check('permissions[2]')
            ->check('permissions[3]')
            ->press('Create')
            ->seePageIs('/admin/access/role')
            ->see('The role was successfully created.')
            ->seeInDatabase($this->roleTable, ['name' => 'Test Role', 'all' => 0]);
       $latestId = Role::orderby('created_at', 'desc')->first()->id;
       $this->seeInDatabase($this->permissionRoleTable, ['permission_id' => 2, 'role_id' => $latestId])
            ->seeInDatabase($this->permissionRoleTable, ['permission_id' => 3, 'role_id' => $latestId]);

        Event::assertDispatched(RoleCreated::class);
    }

    public function testRoleAlreadyExists()
    {
        $this->actingAs($this->admin)
            ->visit('/admin/access/role/create')
            ->type('Administrator', 'name')
            ->press('Create')
            ->seePageIs('/admin/access/role/create')
            ->see('That role already exists. Please choose a different name.');
    }

    public function testRoleRequiresPermission()
    {
      $this->setupDatabase();
      if (config('access.roles.role_must_contain_permission')) {
            $this->actingAs($this->admin)
                ->visit('/admin/access/role/create')
                ->type('Test Role', 'name')
                ->select('custom', 'associated-permissions')
                ->press('Create')
                ->seePageIs('/admin/access/role/create')
                ->see('You must select at least one permission for this role.');
        }
    }

    public function testUpdateRoleRequiredFields()
    {
        $this->actingAs($this->admin)
            ->visit('/admin/access/role/1/edit')
            ->type('', 'name')
            ->press('Update')
            ->seePageIs('/admin/access/role/1/edit')
            ->see('The name field is required.');
    }

    public function testUpdateRoleFormAll()
    {
        // Make sure our events are fired
        Event::fake();

        $this->actingAs($this->admin)
            ->visit('/admin/access/role/1/edit')
            ->type('Administrator Edited', 'name')
            ->type('123', 'sort')
            ->press('Update')
            ->seePageIs('/admin/access/role')
            ->see('The role was successfully updated.')
            ->seeInDatabase($this->roleTable, ['id' => 1, 'name' => 'Administrator Edited', 'sort' => 123]);

        Event::assertDispatched(RoleUpdated::class);
    }

    public function testUpdateRoleFormSpecificPermissions()
    {
        // Make sure our events are fired
        Event::fake();

        $this->actingAs($this->admin)
            ->dontSeeInDatabase($this->permissionRoleTable, ['permission_id' => 2, 'role_id' => 3])
            ->dontSeeInDatabase($this->permissionRoleTable, ['permission_id' => 3, 'role_id' => 3])
            ->visit('/admin/access/role/3/edit')
            ->check('permissions[2]')
            ->check('permissions[3]')
            ->press('Update')
            ->seePageIs('/admin/access/role')
            ->see('The role was successfully updated.')
            ->seeInDatabase($this->permissionRoleTable, ['permission_id' => 2, 'role_id' => 3])
            ->seeInDatabase($this->permissionRoleTable, ['permission_id' => 3, 'role_id' => 3]);

        Event::assertDispatched(RoleUpdated::class);
    }

    public function testUpdateRoleRequiresPermission()
    {
      $this->setupDatabase();
      $this->actingAs($this->admin)
            ->visit('/admin/access/role/3/edit')
            ->press('Update')
            ->seePageIs('/admin/access/role/3/edit')
            ->see('You must select at least one permission for this role.');
    }

    public function testDeleteRoleForm()
    {
        // Make sure our events are fired
        Event::fake();

        $role = factory(Role::class)->create();

        $this->actingAs($this->admin)
            ->seeInDatabase($this->roleTable, ['id' => $role->id])
            ->delete('/admin/access/role/'.$role->id)
            ->assertRedirectedTo('/admin/access/role')
            ->dontSeeInDatabase($this->roleTable, ['id' => $role->id])
            ->seeInSession(['flash_success' => 'The role was successfully deleted.']);

        Event::assertDispatched(RoleDeleted::class);
    }

    public function testDeleteRoleWithPermissions()
    {
        // Make sure our events are fired
        Event::fake();

        // Remove users from role first because it will error on that first
        DB::table($this->roleUserTable)
            ->where('role_id', 2)
            ->delete();

        $this->actingAs($this->admin)
            ->visit('/admin/access/role')
            ->delete('/admin/access/role/2')
            ->assertRedirectedTo('/admin/access/role')
            ->dontSeeInDatabase($this->roleTable, ['id' => 2])
            ->dontSeeInDatabase($this->permissionRoleTable, ['permission_id' => 1, 'role_id' => 2])
            ->dontSeeInDatabase($this->permissionRoleTable, ['permission_id' => 2, 'role_id' => 2])
            ->seeInSession(['flash_success' => 'The role was successfully deleted.']);

        Event::assertDispatched(RoleDeleted::class);
    }

    public function testCanNotDeleteAdministratorRole()
    {
        $this->actingAs($this->admin)
            ->visit('/admin/access/role')
            ->delete('/admin/access/role/1')
            ->assertRedirectedTo('/admin/access/role')
            ->seeInDatabase($this->roleTable, ['id' => 1, 'name' => 'Administrator'])
            ->seeInSession(['flash_danger' => 'You can not delete the Administrator role.']);
    }

    public function testCanNotDeleteRoleWithUsers()
    {
      $this->setupDatabase();
      $this->actingAs($this->admin)
            ->visit('/admin/access/role')
            ->delete('/admin/access/role/2')
            ->assertRedirectedTo('/admin/access/role')
            ->seeInDatabase($this->roleTable, ['id' => 2])
            ->seeInSession(['flash_danger' => 'You can not delete a role with associated users.']);
    }
}
