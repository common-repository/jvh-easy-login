<?php

namespace JVH;

use JsonException;

class EasyLogin {
	/**
	 * EasyLogin constructor.
	 * @throws JsonException
	 */
	public function __construct() {
		$token = $this->getToken();
		if ( $token === null ) {
			return;
		}

		$userData = $this->requestUserInformation( $token );

		$userExists = $this->checkIfUserExists( $userData['data']['user_email'], false );
		if ( ! $userExists ) {
			$this->createUser( $userData );
		}
		$this->loginAsUser( $this->getUserId( $userData['data']['user_email'] ) );
	}

	/**
	 * @return string|null
	 */
	private function getToken(): ?string {
		if ( ! isset( $_GET['jvh-login'] ) && ! isset( $_POST['jvh-login'] ) ) {
			return null;
		}

		$token = $_GET['jvh-login'];
		if ( empty( $token ) ) {
			$token = $_POST['jvh-login'];
		}
		if ( empty( $token ) ) {
			$this->die( 'jvh-login empty' );
		}

		return $token;
	}

	private function die( string $message ): void {
		file_put_contents( __DIR__ . '/incidents.log', '[' . date( 'Y-m-d H:i:s' ) . '] ' . $message . "\n",
			FILE_APPEND );
		wp_die( 'Something went wrong. This incident will be logged.' );

	}

	/**
	 * @param string $token
	 *
	 * @return mixed
	 * @throws JsonException
	 */
	private function requestUserInformation( string $token ) {
		$response = wp_remote_get( 'https://api2.workspace.jvhwebbouw.nl/wp-json/jvh/v1/user/me', [
			'method'      => 'GET',
			'timeout'     => 10000,
			'redirection' => 5,
			'headers'     => [
				'Authorization' => 'Bearer ' . $token,
			],
		] );

		if ( is_wp_error( $response ) ) {
			$this->die( 'Could not get user information: ' . $response->get_error_message() );
		}

		if ( ! isset( $response['body'] ) ) {
			$this->die( 'Could not get user information. Body of call not present' );
		}

		$data = json_decode( $response['body'], true, 512, JSON_THROW_ON_ERROR );
		if ( ! isset( $data['status'] ) || $data['status'] !== 'OK' ) {
			$this->die( 'Could not get user information. Status is not OK, so authentication token must be wrong.' );
		}

		return $data['data'];
	}

	/**
	 * @param string $email
	 *
	 * @param bool $checkExpiration
	 *
	 * @return bool
	 */
	private function checkIfUserExists( string $email, bool $checkExpiration = true ): bool {
		$user = get_user_by( 'email', $email );

		if ( $user !== false && ! is_wp_error( $user ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $userId
	 */
	private function loginAsUser( int $userId ): void {
		$expiration = get_user_meta( $userId, 'jvh-login-expiration', true );
		if ( ! empty( $expiration ) ) {
			update_user_meta( $userId, 'jvh-login-expiration', (int) microtime( true ) + WEEK_IN_SECONDS );
		}

		$user = get_user_by( 'id', $userId );

		wp_clear_auth_cookie();
		wp_set_current_user( $userId );
		wp_set_auth_cookie( $userId, true );
		wp_safe_redirect( user_admin_url() );
		do_action( 'wp_login', $user->user_login, $user, false );
		exit();
	}

	private function getUserId( $email ): int {
		return get_user_by( 'email', $email )->ID;
	}

	/**
	 * @param array $userData
	 *
	 * @return void
	 */
	private function createUser( array $userData ): void {
		$userId = wp_insert_user( [
			'user_login'   => $userData['data']['user_login'] . '@jvh',
			'user_url'     => 'https://www.jvhwebbouw.nl',
			'user_pass'    => md5( microtime( true ) ) . md5( 'JVH' . microtime( true ) ),
			'user_email'   => $userData['data']['user_email'],
			'display_name' => $userData['data']['display_name'],
			'nickname'     => 'JVH webbouw - ' . $userData['data']['display_name'],
			'first_name'   => $userData['data']['meta']['first_name'][0],
			'last_name'    => $userData['data']['meta']['last_name'][0],
			'role'         => 'administrator',
		] );

		if ( is_wp_error( $userId ) ) {
			$this->die( 'Could not create user: ' . $userId->get_error_message() );
		}

	}
}

