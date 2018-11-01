<?php

namespace Wpp\TitanFrameworkRestApi\Endpoints;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use TitanFramework;

class WP_REST_Titan_Framework_Controller extends WP_REST_Controller {

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $version = '1';
        $namespace = 'titanframework/v' . $version;
        $base = 'options';
        register_rest_route($namespace, '/' . $base . '/(?P<namespace>.+)/(?P<option>.+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array($this, 'get_item'),
                'permission_callback' => array($this, 'get_item_permissions_check'),
                'args' => array(
                    'context' => array(
                        'default' => 'view',
                    ),
                ),
            ),
        ));
        register_rest_route($namespace, '/' . $base . '/schema', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_public_item_schema'),
        ));
    }

    /**
     * Get one item from the collection
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_item($request) {
        //get parameters from request
        $params = $request->get_params();

        $titan = TitanFramework::getInstance($params['namespace']);
        $option = $titan->getOption($params['option']);

		$data = $this->prepare_item_for_response( $option, $request );

		return rest_ensure_response( $data );
    }

    /**
     * Check if a given request has access to get a specific item
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_item_permissions_check($request) {
        return apply_filters(
            'rest_get_item_permissions_check_titanframework_option',
            current_user_can('manage_options'),
            $request->get_param('namespace'),
            $request->get_param('option'));
    }

	/**
	 * Prepares the option for serialization.
	 *
	 * @param string          $option  Option value from database.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Option data (only the value).
	 */
	public function prepare_item_for_response( $option, $request ) {

		$fields = $this->get_fields_for_response( $request );
		$data   = array();

		if ( in_array( 'value', $fields, true ) ) {
			$data['value'] = $option;
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		/**
		 * Filters an option (value) returned from the REST API.
		 *
		 * Allows modification of the option (value) right before it is returned.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param object           $status   The original option data array.
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_titanframework_option', $response, $option, $request );
	}

	/**
	 * Retrieves the option's schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'option',
			'type'                 => 'object',
			'properties'           => array(
				'value'            => array(
					'description'  => __( 'Value for the option.' ),
					'type'         => 'string',
					'context'      => array( 'embed', 'view' ),
                    'readonly'     => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

}
