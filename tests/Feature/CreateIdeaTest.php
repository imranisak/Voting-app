<?php

namespace Tests\Feature;

use App\Http\Livewire\CreateIdea;
use App\Models\Category;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class CreateIdeaTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function ideaFormDoesNotShowWhenLoggedOut(){

        $response=$this->get(route("idea.index"));
        $response->assertSuccessful();
        $response->assertSee("Please log in to submit an idea!");
        $response->assertDontSee("Let us know what you would like and we'll take a look over!", false);
    }
    /** @test */
    public function ideaFormDoesShowWhenLoggedIn(){

        $response=$this->actingAs(User::factory()->create())->get(route("idea.index"));
        $response->assertSuccessful();
        $response->assertDontSee("Please log in to submit an idea!");
        $response->assertSee("Let us know what you would like and we'll take a look over!", false);
    }

    /** @test */
    public function mainPageContainsIdeaFormLivewireComponent(){
        $this->actingAs(User::factory()->create())
            ->get(route("idea.index"))
            ->assertSeeLivewire('create-idea');
    }

    /** @test */
    public function createIdeaFormValidationWorks(){
        Livewire::actingAs(User::factory()->create())
            ->test(CreateIdea::class)
            ->set('title', '')
            ->set('category', '')
            ->set('description', '')
            ->call('createIdea')
            ->assertHasErrors(['title', 'category', 'description']);
    }

    /** @test */
    public function createAnIdeaWorksProperly(){
        $user = User::factory()->create();

        $categoryOne = Category::factory()->create(['name' => 'Category 1']);

        $statusOpen = Status::factory()->create(['name' => 'Open']);

        Livewire::actingAs($user)
            ->test(CreateIdea::class)
            ->set('title', 'I should get a job')
            ->set('category', $categoryOne->id)
            ->set('description', 'I need a job!')
            ->call('createIdea')
            ->assertRedirect(route('idea.index'));
        $response=$this->actingAs($user)->get(route('idea.index'));
        $response->assertSuccessful();
        $response->assertSee("I should get a job");
        $response->assertSee("I need a job!");

        $this->assertDatabaseHas('ideas', [
            'title'=>'I should get a job'
        ]);
    }

    /** @test */
    public function creatingTwoIdeasWithSameTitleStillWorksButHasDifferentSlugs()
    {
        $user = User::factory()->create();

        $categoryOne = Category::factory()->create(['name' => 'Category 1']);

        $statusOpen = Status::factory()->create(['name' => 'Open']);

        Livewire::actingAs($user)
            ->test(CreateIdea::class)
            ->set('title', 'My First Idea')
            ->set('category', $categoryOne->id)
            ->set('description', 'This is my first idea')
            ->call('createIdea')
            ->assertRedirect('/');

        $this->assertDatabaseHas('ideas', [
            'title' => 'My First Idea',
            'slug' => 'my-first-idea'
        ]);

        Livewire::actingAs($user)
            ->test(CreateIdea::class)
            ->set('title', 'My First Idea')
            ->set('category', $categoryOne->id)
            ->set('description', 'This is my first idea')
            ->call('createIdea')
            ->assertRedirect('/');

        $this->assertDatabaseHas('ideas', [
            'title' => 'My First Idea',
            'slug' => 'my-first-idea-2'
        ]);
    }
}
