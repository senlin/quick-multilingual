<?php
declare( strict_types=1 );

namespace SOWP\QuickMultilingual\Api;

/**
 * REST endpoint: GET /wp-json/so-qmp/v1/pages
 *
 * Query params:
 *   ancestor (int, required for secondary scope) – returns all published
 *             descendants at any depth via get_pages( child_of ).
 *   search   (string, optional)                  – title substring filter.
 */
final class PagesEndpoint {

	public function register(): void {
		register_rest_route(
			'so-qmp/v1',
			'/pages',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'handle' ],
				'permission_callback' => static function (): bool {
					return current_user_can( 'edit_posts' );
				},
				'args' => [
					'ancestor' => [
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'default'           => 0,
					],
					'search' => [
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
						'default'           => '',
					],
				],
			]
		);
	}

	/** @return \WP_REST_Response|\WP_Error */
	public function handle( \WP_REST_Request $request ) {
		$ancestor = $request->get_param( 'ancestor' );
		$search   = $request->get_param( 'search' );

		$args = [
			'post_status' => 'publish',
			'sort_column' => 'post_title',
			'sort_order'  => 'ASC',
			'number'      => 100,
		];

		if ( $ancestor > 0 ) {
			$args['child_of'] = $ancestor;
		}

		if ( '' !== $search ) {
			$args['search'] = $search;
		}

		$pages = get_pages( $args );
		if ( ! is_array( $pages ) ) {
			$pages = [];
		}

		$data = array_map(
			static function ( \WP_Post $page ): array {
				return [
					'id'    => $page->ID,
					'title' => $page->post_title,
				];
			},
			$pages
		);

		return rest_ensure_response( $data );
	}
}
