<?
namespace Concrete\Core\Foundation\Collection\Database\Column;
use Loader;
class Column {

	public function getColumnValue($obj) {
		if (is_array($this->callback)) {
			return call_user_func($this->callback, $obj);
		} else {
			return call_user_func(array($obj, $this->callback));
		}
	}
	
	public function getColumnKey() {return $this->columnKey;}
	public function getColumnName() {return $this->columnName;}
	public function getColumnDefaultSortDirection() {return $this->defaultSortDirection;}
	public function isColumnSortable() {return $this->isSortable;}
	public function getColumnCallback() {return $this->callback;}
	public function setColumnDefaultSortDirection($dir) {$this->defaultSortDirection = $dir;}
	public function __construct($key, $name, $callback, $isSortable = true, $defaultSort = 'asc') {
		$this->columnKey = $key;
		$this->columnName = $name;
		$this->isSortable = $isSortable;
		$this->callback = $callback;
		$this->defaultSortDirection = $defaultSort;
	}
}