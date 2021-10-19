<?php

namespace Tests\Feature;

use App\Models\Idea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowIdeasTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function listOfIdeasShowsOnMainPage(){
        $ideaOne=Idea::factory()->create([
            'title'=>'My first idea',
            'description'=>"Description of my first idea"
        ]);
        $ideaTwo=Idea::factory()->create([
            'title'=>'My second idea',
            'description'=>"Description of my second idea"
        ]);

        $response=$this->get(route('idea.index'));

        $response->assertSuccessful();
        $response->assertSee($ideaOne->title);
        $response->assertSee($ideaOne->description);
        $response->assertSee($ideaTwo->title);
        $response->assertSee($ideaTwo->description);
    }

    /** @test */
    public function singleIdeaShowsCorrectllyOnTheShowPage(){
        $idea=Idea::factory()->create([
            'title'=>'My first idea',
            'description'=>"Description of my first idea"
        ]);

        $response=$this->get(route('idea.show', $idea));

        $response->assertSuccessful();
        $response->assertSee($idea->title);
        $response->assertSee($idea->description);
    }

    /** @test */
    public function ideasPaginationWorks(){
        Idea::factory(Idea::PAGINATION_COUNT+1)->create();

        $ideaOne=Idea::find(1);
        $ideaOne->title="My first idea";
        $ideaOne->save();

        $ideaEleven=Idea::find(Idea::PAGINATION_COUNT + 1);
        $ideaEleven->title="My eleventh idea";
        $ideaEleven->save();

        $response=$this->get('/');

        $response->assertSee($ideaOne->title);
        $response->assertDontSee($ideaEleven->title);

        $response=$this->get("/?page=2");

        $response->assertSee($ideaEleven->title);
        $response->assertDontSee($ideaOne->title);
    }

    /** @test */
    public function sameIdeaTitleDifferentSlug(){
        $ideaOne = Idea::factory()->create([
            'title' => 'My First Idea',
            'description' => 'Description for my first idea',
        ]);

        $ideaTwo = Idea::factory()->create([
            'title' => 'My First Idea',
            'description' => 'Another Description for my first idea',
        ]);

        $response = $this->get(route('idea.show', $ideaOne));

        $response->assertSuccessful();
        $this->assertTrue(request()->path() === 'ideas/my-first-idea');

        $response = $this->get(route('idea.show', $ideaTwo));

        $response->assertSuccessful();
        $this->assertTrue(request()->path() === 'ideas/my-first-idea-2');
    }
}
