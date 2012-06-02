<?php

abstract class Model {
	protected static function getColumnsForSelectBase($columns, $column_prefix, $table_alias = false)
	{
		$aliased = array();
		foreach ($columns as $col) {
			$item = '';

			if ($table_alias) {
				$item = "`{$table_alias}`.";
			}

			$item .= "`{$col}` AS `{$column_prefix}{$col}`";

			$aliased[] = $item;
		}

		return implode(', ', $aliased);
	}

	protected function populateFromMap($column_map, $column_prefix, $map) {
		foreach ($column_map as $column => $property) {
			$prefixed_column = $column_prefix . $column;

			if (isset($map[$prefixed_column])) {
				$this->$property = $map[$prefixed_column];
			}
		}
	}
}

?>
