<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Idea;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowIdeasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function listOfIdeasShowsOnMainPage(){

        $user=User::factory()->create();

        $statusOpen = Status::factory()->create(['name' => 'Open', 'classes' => 'bg-gray-200']);
        $statusConsidering = Status::factory()->create(['name' => 'Considering', 'classes' => 'bg-purple text-white']);

        $categoryOne=Category::factory()->create([
            'name'=>'Category 1'
        ]);
        $categoryTwo=Category::factory()->create([
            'name'=>'Category 2'
        ]);

        $ideaOne=Idea::factory()->create([
            'user_id'=>$user->id,
            'title'=>'My first idea',
            'category_id'=>$categoryOne->id,
            'status_id'=>$statusOpen->id,
            'description'=>"Description of my first idea"
        ]);
        $ideaTwo=Idea::factory()->create([
            'user_id'=>$user->id,
            'title'=>'My second idea',
            'category_id'=>$categoryTwo->id,
            'status_id'=>$statusConsidering->id,
            'description'=>"Description of my second idea"
        ]);

        $response=$this->get(route('idea.index'));

        $response->assertSuccessful();
        $response->assertSee($ideaOne->title);
        $response->assertSee($ideaOne->description);
        $response->assertSee($categoryOne->name);
        $response->assertSee('<div class="bg-gray-200', false);

        $response->assertSee($ideaTwo->title);
        $response->assertSee($ideaTwo->description);
        $response->assertSee($categoryTwo->name);
        $response->assertSee('<div class="bg-purple', false);
    }

    /** @test */
    public function singleIdeaShowsCorrectlyOnTheShowPage(){
        $statusOpen = Status::factory()->create(['name' => 'Open', 'classes' => 'bg-gray-200']);

        $user=User::factory()->create();


        $categoryOne=Category::factory()->create([
            'name'=>'Category 1'
        ]);
        $idea=Idea::factory()->create([
            'user_id'=>$user->id,
            'title'=>'My first idea',
            'category_id'=>$categoryOne->id,
            'status_id'=>$statusOpen->id,
            'description'=>"Description of my first idea"
        ]);

        $response=$this->get(route('idea.show', $idea));

        $response->assertSuccessful();
        $response->assertSee($idea->title);
        $response->assertSee($idea->description);
        $response->assertSee($categoryOne->name);
        $response->assertSee('<div class="bg-gray-200', false);

    }

    /** @test */
    public function ideasPaginationWorks(){
        $statusOpen = Status::factory()->create(['name' => 'Open', 'classes' => 'bg-gray-200']);

        $user=User::factory()->create();

        $categoryOne=Category::factory()->create([
            'name'=>'Category 1'
        ]);
        Idea::factory(Idea::PAGINATION_COUNT+1)->create([
            'user_id'=>$user->id,
            'category_id'=>$categoryOne->id,
            'status_id' => $statusOpen->id,
        ]);

        $ideaOne=Idea::find(1);
        $ideaOne->title="My first idea";
        $ideaOne->save();

        $ideaEleven=Idea::find(Idea::PAGINATION_COUNT + 1);
        $ideaEleven->title="My eleventh idea";
        $ideaEleven->save();

        $response=$this->get('/');

        $response->assertSee($ideaEleven->title);
        $response->assertDontSee($ideaOne->title);

        $response=$this->get("/?page=2");

        $response->assertSee($ideaOne->title);
        $response->assertDontSee($ideaEleven->title);
    }

    /** @test */
    public function sameIdeaTitleDifferentSlug(){
        $statusOpen = Status::factory()->create(['name' => 'Open', 'classes' => 'bg-gray-200']);
        $categoryOne=Category::factory()->create([
            'name'=>'Category 1'
        ]);
        $categoryTwo=Category::factory()->create([
            'name'=>'Category 2'
        ]);

        $user=User::factory()->create();


        $ideaOne = Idea::factory()->create([
            'user_id'=>$user->id,
            'title' => 'My First Idea',
            'category_id'=>$categoryOne->id,
            'status_id' => $statusOpen->id,
            'description' => 'Description for my first idea',
        ]);

        $ideaTwo = Idea::factory()->create([
            'user_id'=>$user->id,
            'title' => 'My First Idea',
            'category_id'=>$categoryTwo->id,
            'status_id' => $statusOpen->id,
            'description' => 'Another Description for my first idea',
        ]);

        $response = $this->get(route('idea.show', $ideaOne));

        $response->assertSuccessful();
        $this->assertTrue(request()->path() === 'ideas/my-first-idea');

        $response = $this->get(route('idea.show', $ideaTwo));

        $response->assertSuccessful();
        $this->assertTrue(request()->path() === 'ideas/my-first-idea-2');
    }

    /** @test */
    public function inAppBackButtonWorksWhenIndexPageVisitedFirst()
    {
        $user = User::factory()->create();

        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $statusOpen = Status::factory()->create(['name' => 'Open', 'classes' => 'bg-gray-200']);
        $statusConsidering = Status::factory()->create(['name' => 'Considering', 'classes' => 'bg-purple text-white']);

        $ideaOne = Idea::factory()->create([
            'user_id' => $user->id,
            'title' => 'My First Idea',
            'category_id' => $categoryOne->id,
            'status_id' => $statusOpen->id,
            'description' => 'Description of my first idea',
        ]);


        $response = $this->get('/?category=Category%202&status=Considering');
        $response = $this->get(route('idea.show', $ideaOne));

        $this->assertStringContainsString('/?category=Category%202&status=Considering', $response['backLink']);
    }

    /** @test */
    public function inAppBackButtonWorksWhenShowPageOnlyPageVisited()
    {
        $user = User::factory()->create();

        $categoryOne = Category::factory()->create(['name' => 'Category 1']);
        $categoryTwo = Category::factory()->create(['name' => 'Category 2']);

        $statusOpen = Status::factory()->create(['name' => 'Open', 'classes' => 'bg-gray-200']);
        $statusConsidering = Status::factory()->create(['name' => 'Considering', 'classes' => 'bg-purple text-white']);

        $ideaOne = Idea::factory()->create([
            'user_id' => $user->id,
            'title' => 'My First Idea',
            'category_id' => $categoryOne->id,
            'status_id' => $statusOpen->id,
            'description' => 'Description of my first idea',
        ]);

        $response = $this->get(route('idea.show', $ideaOne));

        $this->assertEquals(route('idea.index'), $response['backLink']);
    }
}
