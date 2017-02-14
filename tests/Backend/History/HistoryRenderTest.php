<?php

namespace Tests\Backend\History;

use Tests\TestCase;
use App\Repositories\Backend\History\Facades\History;

/**
 * Class HistoryRenderTest.
 */
class HistoryRenderTest extends TestCase
{
    public function testDashboardDisplaysHistory()
    {
        $this->actingAs($this->admin);

        history()
            ->withType('User')
            ->withText(trans('history.backend.users.created').$this->user->name)
            ->withEntity($this->user->id)
            ->withIcon('plus')
            ->withClass('bg-green')
            ->log();

        $response = $this->get('/admin/dashboard')
             ->see('<strong>'.$this->admin->name.'</strong> '.trans('history.backend.users.created').$this->user->name);
    }

    public function testTypeDisplaysHistory()
    {
        $this->actingAs($this->admin);

        history()
            ->withType('User')
            ->withText(trans('history.backend.users.created').$this->user->name)
            ->withEntity($this->user->id)
            ->withIcon('plus')
            ->withClass('bg-green')
            ->log();

        $response = $this->get('/admin/access/user')
             ->see('<strong>'.$this->admin->name.'</strong> '.trans('history.backend.users.created').$this->user->name);
    }

    public function testEntityDisplaysHistory()
    {
        $this->actingAs($this->admin);

        history()
            ->withType('User')
            ->withText(trans('history.backend.users.created').$this->user->name)
            ->withEntity($this->user->id)
            ->withIcon('plus')
            ->withClass('bg-green')
            ->log();

        $response = $this->get('/admin/access/user/3')
             ->see('<strong>'.$this->admin->name.'</strong> '.trans('history.backend.users.created').$this->user->name);
    }
}
