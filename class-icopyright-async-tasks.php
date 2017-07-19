<?php
include (WP_PLUGIN_DIR . "/copyright-licensing-tools/wp-async-task/wp-async-task.php");

class ICopy_Save_Post_Task extends WP_Async_Task {

	/**
	 * Action to use to trigger this task
	 *
	 * @var string
	 */
	protected $action = 'save_post';

	/**
	 * Prepare POST data to send to session that processes the task
	 *
	 * @param array $data Params from hook
	 *
	 * @return array
	 */
	protected function prepare_data($data){
		return array(
				'post_id' => $data[0],
				'post' => $data[1]
		);
	}

	/**
	 * Run the asynchronous task
	 *
	 * Calls send_to_api()
	 */
	protected function run_action() {
		if( isset( $_POST[ 'post_id' ] ) && 0 < absint( $_POST[ 'post_id' ] ) ){
			do_action( "wp_async_$this->action", $_POST[ 'post_id' ], $_POST[ 'post' ] );
		}

	}
}


class ICopy_Delete_Post_Task extends WP_Async_Task {

	/**
	 * Action to use to trigger this task
	 *
	 * @var string
	 */
	protected $action = 'before_delete_post';

	/**
	 * Prepare POST data to send to session that processes the task
	 *
	 * @param array $data Params from hook
	 *
	 * @return array
	 */
	protected function prepare_data($data){
		return array(
				'post_id' => $data[0]
		);
	}

	/**
	 * Run the asynchronous task
	 *
	 * Calls send_to_api()
	 */
	protected function run_action() {
		if( isset( $_POST[ 'post_id' ] ) && 0 < absint( $_POST[ 'post_id' ] ) ){
			do_action( "wp_async_$this->action", $_POST[ 'post_id' ]);
		}

	}
}


class ICopy_Get_Widget_Articles extends WP_Async_Task {
	/**
	 * Action to use to trigger this task
	 *
	 * @var string
	 */
	protected $action = 'icopy_get_widget_articles';
	
	/**
	 * Prepare POST data to send to session that processes the task
	 *
	 * @param array $data Params from hook
	 *
	 * @return array
	 */
	protected function prepare_data($data){
		return array(
				'numResults' => $data[0],
				'instance' => $data[1]
		);
	}
	
	/**
	 * Run the asynchronous task
	 *
	 * Calls send_to_api()
	 */
	protected function run_action() {

		echo("run action");
		if( isset( $_POST[ 'numArticles' ] ) && 0 < absint( $_POST[ 'numArticles' ] ) ){
			do_action( "wp_async_$this->action", $_POST[ 'numResults' ], $_POST['instance']);
		}
	
	}	
	
	
}