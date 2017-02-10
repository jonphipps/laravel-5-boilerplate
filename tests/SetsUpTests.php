<?php

/** Created by PhpStorm,  User: jonphipps,  Date: 2017-02-09,  Time: 12:34 PM */

namespace Tests;

use App\Models\Access\Role\Role;
use App\Models\Access\User\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

trait SetsUpTests
{

  /**
   * The base URL to use while testing the application.
   *
   * @var string
   */
  protected $baseUrl = 'http://l5boilerplate.dev';

  protected $admin;

  /** @var  User $executive */
  protected $executive;

  /** @var  User $user */
  protected $user;

  /** @var  Role $adminRole */
  protected $adminRole;

  /** @var  Role $executiveRole */
  protected $executiveRole;

  /** @var  Role $userRole */
  protected $userRole;

  public static $setupDatabase = true;

  protected $userTable;
  protected $roleUserTable;
  protected $roleTable;
  protected $permissionRoleTable;


  /**
   * Set up tests.
   */
  public function setUp()
  {
    parent::setUp();

    // Run the tests in English
    App::setLocale('en');

    if (self::$setupDatabase) {
      $this->setupDatabase();
    }

    ini_restore('arg_separator.output');

    /*
     * Create class properties to be used in tests
     */
    $this->admin         = User::find(1);
    $this->executive     = User::find(2);
    $this->user          = User::find(3);
    $this->adminRole     = Role::find(1);
    $this->executiveRole = Role::find(2);
    $this->userRole      = Role::find(3);

    /** Define tables */

    $this->userTable           = config('access.users_table');
    $this->roleUserTable       = config('access.role_user_table');
    $this->roleTable           = config('access.roles_table');
    $this->permissionRoleTable = config('access.permission_role_table');
  }


  public function setupDatabase()
  {
    // Set up the database
    Artisan::call('migrate:refresh');
    Artisan::call('db:seed');

    self::$setupDatabase = false;
  }

}
