<?php
use Carbon_Fields\Field\Field;

class FieldConditionalLogicTest extends WP_UnitTestCase {
	private $field;

	function setup() {
		$this->field = Field::make('text', 'color');
	}

	function tearDown() {
		unset($this->field);
	}



	/**
	 * @expectedException Carbon_Fields\Exception\Incorrect_Syntax_Exception
	 * @expectedExceptionMessage should be an array
	 */	
	function testErrorIsThrownWhenCondLogicIsNotArray() {
		$this->field->set_conditional_logic("this should actually be array");
	}

	/**
	 * @expectedException Carbon_Fields\Exception\Incorrect_Syntax_Exception
	 */	
	function testErrorIsThrownWhenFlatArrayIsProvided() {
		$this->field->set_conditional_logic([
			'field' => 'is_product',
			'value' => 'yes',
		]);
	}

	/**
	 * Private helper method for brevity
	 */
	function verify_cond_logic($user_defined_cond_logic, $expected_parsed_cond_logic) {
		$actual_parsed_cond_logic = $this->field
			->set_conditional_logic($user_defined_cond_logic)
			->get_conditional_logic();

		$this->assertEquals(
			$expected_parsed_cond_logic,
			$actual_parsed_cond_logic
		);
	}

	function testBasicCondLogic() {
		$user_defined_cond_logic = [
			[
				'field' => 'is_product',
				'value' => 'yes',
			]
		];

		$expected_parsed_cond_logic = [
			'relation' => 'AND',
			'rules' => [
				[
					'field' => 'is_product',
					'value' => 'yes',
					'compare' => '=',
				]
			]
		];
		$this->verify_cond_logic(
			$user_defined_cond_logic,
			$expected_parsed_cond_logic
		);
	}

	function testValueDefaultsToEmptyString() {
		$user_defined_cond_logic = [
			[
				'field' => 'is_product',
				'compare' => '!=',
			]
		];

		$expected_parsed_cond_logic = [
			'relation' => 'AND',
			'rules' => [
				[
					'field' => 'is_product',
					'value' => '',
					'compare' => '!=',
				]
			]
		];

		$this->verify_cond_logic(
			$user_defined_cond_logic,
			$expected_parsed_cond_logic
		);
	}

	function testRelationOperatorIsProvidedInLowercase() {
		$user_defined_cond_logic = [
			'relation' => 'or',
			[
				'field' => 'is_product',
				'value' => 'yes',
			]
		];

		$expected_parsed_cond_logic = [
			'relation' => 'OR',
			'rules' => [
				[
					'field' => 'is_product',
					'value' => 'yes',
					'compare' => '=',
				]
			]
		];
		$this->verify_cond_logic(
			$user_defined_cond_logic,
			$expected_parsed_cond_logic
		);

	}

	function testBadRelationOperatorThrowsError() {
		$this->field->set_conditional_logic([
			'relation' => 'maybe',
			[
				'field' => 'is_product',
				'value' => 'yes',
			]
		]);
	}

	/**
	 * @expectedException Carbon_Fields\Exception\Incorrect_Syntax_Exception
	 * @expectedExceptionMessage compare operator
	 */
	function testBadCompareOperatorThrowsError() {
		$this->field->set_conditional_logic([
			[
				'field' => 'is_product',
				'value' => 'yes',
				'compare' => '!==' // There is no `!==` operator
			]
		]);
	}

	/**
	 * @expectedException Carbon_Fields\Exception\Incorrect_Syntax_Exception
	 * @expectedExceptionMessage An array is expected
	 */
	function testInCompareOperatorRequiresArrayAsValue() {
		$this->field->set_conditional_logic([
			[
				'field' => 'is_product',
				'value' => 'yes',
				'compare' => 'IN'
			]
		]);

	}
}