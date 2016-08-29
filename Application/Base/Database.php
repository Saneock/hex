<?php
namespace Hex\Base;

/**
 * Работа с базой данных
 *
 * Class Database
 * @package Base
 */
class Database extends \Abstracts\Singleton
{
    protected static $instance;

    protected static $db;
    protected static $cache;
	public static $queries = 0;

	protected function __construct()
    {
		self::$db = $this->databaseConnect();
		self::$cache = $this->memcachedConnect();
    }

	/**
     * Подключение к базе данных
     *
     * @return PDO
     */
	protected function databaseConnect()
	{	
		$dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_ENCODING;

		$opt = array(
			\PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
			\PDO::ATTR_ORACLE_NULLS 	  => \PDO::NULL_EMPTY_STRING
		);

		try {
			$db = new \PDO($dsn, DB_USER, DB_PASS, $opt);

			$db->exec('SET NAMES '.DB_ENCODING);
			$db->exec('SET CHARACTER SET '.DB_ENCODING);
			$db->exec('SET COLLATION_CONNECTION="'.DB_COLLATION.'"');
			$db->exec('SET time_zone = "'.DB_TIMEZONE.'"');
		} catch (\PDOException $ex) {
			throw new \Exception\Database\Connect('Connect Error', $ex->getCode(), $ex);
		}

		return $db;
	}

	/**
     * Подключение серверу кэширования
     *
     * @return Memcached
     */
	protected function memcachedConnect()
	{	
		$cache = new \Memcache();
		$cache->pconnect('127.0.0.1', 11211) or die ("Could not connect");
		
		return $cache;
	}



	/**
     * Выполнение стандартного Query запроса
     *
	 * Если передать параметр $variables, скрипт будет использовать подготовленные выражения.
	 *
     * @param string $sql_query SQL запрос
     * @param bool|array $variables Подготовленные переменные
     * @return PDOStatement
     */
	public static function query($sql_query, $variables = false)
	{
		try {
			if (!$variables) {
				// Использование обычного выражения
				$q = self::$db->query($sql_query);
			} else {
				// Использование подготовленного выражения

				$stmt = self::$db->prepare($sql_query);
			
				$stmt->execute($variables);
					
				$q = $stmt;
			}

		} catch (\mysqli_sql_exception $e) {
			throw new \Exception\Database\Query($sql_query, $e->getCode(), $e);
		}
		
		// Увеличиваем счётчик запросов
		self::$queries++;

		return $q;
	}

	/**
     * Возвращает строку результата запроса
     *
     * @param PDOStatement $query
     * @param int $fetch_style
     * @return array|false False в случае ошибки
     */
	public static function fetch(\PDOStatement &$query, $fetch_style = null)
	{
		try {
			$res = $query->fetch($fetch_style);
			
			if (!$res) {
				$query->closeCursor();
			}
		} catch (\mysqli_sql_exception $e) {
			throw new \Exception\Database\Fetch($e->getMessage(), $e->getCode(), $e);
		}

		return $res;
	}

	/**
     * Возвращает весь результат запроса
     *
     * @param PDOStatement|string $query
     * @param int $fetch_style
     * @return array|false False в случае ошибки
     */
	public static function getAll($query, $fetch_style = null)
	{
		try {
			if(is_string($query))
				$query = self::query($query);

			$array = $query->fetchAll($fetch_style);
		} catch (\mysqli_sql_exception $e) {
			throw new \Exception\Database\Fetch($e->getMessage(), $e->getCode(), $e);
		}

		return $array;
	}

	/**
     * Экранирует данные
     *
	 * Доступные типы:
	 * 's' - string, 'i' - integer, 'd' - double, 'a' - array
	 *
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
	public static function escape($value, $type = 's')
	{
		if ($type == 's') {
			return self::$db->quote($value);
		} elseif ($type == 'i') {
			return intval($value);
		} if ($type == 'd') {
			return floatval($value);
		} if ($type == 'a') { // Array
			if(!is_array($value))
				$value = array();
			return self::$db->quote(serialize($value));
		} else {
			throw new \Exception\Logic\BadArgument('type', $type, "Allowed values: 's' (string), 'i' (integer), 'd' (double), 'a' (array)");
		}

		return $value;
	}
}