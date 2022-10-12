<?php
/**
 * Class SampleTest
 *
 * @package Storipress
 */

/**
 * Sample test case.
 */
class ExportTest extends WP_UnitTestCase {
	
	/**
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_export_site_config(): void {
		update_option('blogname', 'Test');
		
		update_option('blogdescription', 'Test Blog');
		
		$storipress = new Storipress();
		
		$export = new ReflectionMethod(Storipress::class, 'export_site_config');
		
		$export->setAccessible( true );
		
		ob_start();
		
		$export->invoke($storipress);
		
		$encode = ob_get_clean();
		
		$json = json_decode($encode, true);
		
		$data = $json['data'];
		
		$this->assertEquals('site', $json['type']);
		
		$this->assertEquals('http://example.org', $data['url']);
		
		$this->assertEquals('Test', $data['name']);
		
		$this->assertEquals('Test Blog', $data['description']);
	}
	
	/**
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_export_users(): void {
		$userId = $this->factory()->user->create([
			'user_nicename' => 'test-nicename',
			'display_name' => 'Test Display Name',
			'user_email' => 'test@example.org',
			'user_url' => 'http://example.org/test',
		]);
		
		$storipress = new Storipress();
		
		$export = new ReflectionMethod(Storipress::class, 'export_users');
		
		$export->setAccessible( true );
		
		ob_start();
		
		$export->invoke($storipress);
		
		$lines = ob_get_clean();
		
		foreach (explode(PHP_EOL, $lines) as $line) {
			if (empty($line)) {
				continue;
			}
			
			$json = json_decode($line, true);
			
			$data = $json['data'];
			
			if ($data['ID'] === $userId) {
				break;
			}
		}
		
		$this->assertEquals('user', $json['type']);
		
		$this->assertEquals('Test Display Name', $data['display_name']);
		
		$this->assertEquals('http://example.org/test', $data['user_url']);
		
		$this->assertEquals('test-nicename', $data['user_nicename']);
	}
	
	/**
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_export_categories(): void {
		$postId = $this->factory()->post->create();
		
		$categoryParentId = $this->factory()->category->create();

		$categoryId = $this->factory()->category->create([
			'name' => 'test',
			'slug' => 'test-category',
			'description' => 'test description',
			'parent' => $categoryParentId
		]);
		
		wp_set_post_categories($postId, [$categoryId]);

		$storipress = new Storipress();
		
		$export = new ReflectionMethod(Storipress::class, 'export_categories');
		
		$export->setAccessible( true );
		
		ob_start();
		
		$export->invoke($storipress);
		
		$lines = ob_get_clean();
		
		foreach (explode(PHP_EOL, $lines) as $line) {
			if (empty($line)) {
				continue;
			}

			$json = json_decode($line, true);

			$data = $json['data'];
			
			if ($data['term_id'] === $categoryId) {
				break;
			}
		}
		
		$this->assertEquals('category', $json['type']);
		
		$this->assertEquals('test', $data['name']);
		
		$this->assertEquals('test-category', $data['slug']);
		
		$this->assertEquals('test description', $data['description']);
		
		$this->assertEquals(1, $data['count']);
		
		$this->assertEquals($categoryParentId, $data['parent']);
	}
	
	/**
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_export_tags(): void {
		$postId = $this->factory()->post->create();
		
		$tagParentId = $this->factory()->tag->create();
		
		$tagId = $this->factory()->tag->create([
			'name' => 'test',
			'slug' => 'test-tags',
			'description' => 'test description',
			'parent' => $tagParentId
		]);
		
		wp_add_post_tags($postId, [$tagId]);
		
		$storipress = new Storipress();
		
		$export = new ReflectionMethod(Storipress::class, 'export_tags');
		
		$export->setAccessible(true);
		
		ob_start();
		
		$export->invoke($storipress);
		
		$lines = ob_get_clean();
		
		foreach (explode(PHP_EOL, $lines) as $line) {
			if (empty($line)) {
				continue;
			}
			
			$json = json_decode($line, true);
			
			$data = $json['data'];
			
			if ($data['term_id'] === $tagId) {
				break;
			}
		}
		
		$this->assertEquals('tag', $json['type']);
		
		$this->assertEquals('test', $data['name']);
		
		$this->assertEquals('test-tags', $data['slug']);
		
		$this->assertEquals('test description', $data['description']);
		
		$this->assertEquals(1, $data['count']);
		
		$this->assertEquals($tagParentId, $data['parent']);
	}
	
	/**
	 * @return void
	 * @throws ReflectionException
	 */
	public function test_export_posts(): void {
		$storipress = new Storipress();
		
		$export = new ReflectionMethod(Storipress::class, 'export_posts');
		
		$export->setAccessible(true);
		
		$userId = $this->factory()->user->create();
		
		$categoryId = $this->factory()->category->create();
		
		$tagId = $this->factory()->tag->create();
		
		$postParentId = $this->factory()->post->create();
		
		$postId = $this->factory()->post->create([
			'post_author' => $userId,
			'post_title' => 'Test Title',
			'post_parent' => $postParentId,
			'post_excerpt' => 'Test post excerpt',
			'post_content' => 'Test Content',
			'post_status' => 'publish',
		]);
		
		wp_set_post_categories($postId, [$categoryId]);
		
		wp_add_post_tags($postId, [$tagId]);
		
		$post = get_post($postId);
		
		$categories = wp_list_pluck(get_the_terms($post, 'category'), 'term_id');
		
		$tags = wp_list_pluck(get_the_terms($post, 'post_tag'), 'term_id');

		ob_start();
		
		$export->invoke($storipress);
		
		$lines = ob_get_clean();
		
		foreach (explode(PHP_EOL, $lines) as $line) {
			if (empty($line)) {
				continue;
			}
			
			$json = json_decode($line, true);
			
			$data = $json['data'];
			
			if ($data['id'] === $postId) {
				break;
			}
		}
		
		$this->assertEquals('post', $json['type']);

		$this->assertEquals($userId, $data['author_id']);
		
		$this->assertEquals('Test Title', $data['title']);
		
		$this->assertEquals('test-title', $data['slug']);
		
		$this->assertEquals('Test post excerpt', $data['excerpt']);
		
		$this->assertEquals(wpautop('Test Content'), $data['content']);
		
		$this->assertEquals('publish', $data['status']);
		
		$this->assertEquals($postParentId, $data['post_id']);
		
		$this->assertEquals($categories, [$categoryId]);
		
		$this->assertEquals($tags, [$tagId]);
	}
}
