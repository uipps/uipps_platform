<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var \Illuminate\Database\Query\Builder
     */
    protected $query = null;

    /**
     * 最后一次执行的sql
     *
     * @var string
     */
    protected $lastSql = '';

    /**
     * update/delete 生效条目
     *
     * @var int
     */
    protected $affectedRows = null;

    /**
     * 执行后是否保留query
     */
    protected $holdQuery = false;

    /**
     * 获取一行
     *
     * @param array $columns
     *
     * @return array
     */
    public function find($columns = ['*'])
    {
        $result = $this->getQuery()->first($columns);
        //记录sql
        $this->setLastSql();
        if ($result instanceof Model) {
            return $result->toArray();
        }
        return [];
    }

    /**
     * 哪个字段不为空
     *
     * @param $field 字段
     *
     * @return $this
     */
    public function whereNotNull($field)
    {
        $this->getQuery()->whereNotNull($field);
        $this->setLastSql();
        return $this;
    }

    /**
     * 根据主键获取一行
     *
     * @param       $id
     * @param array $columns
     *
     * @return array
     */
    public function findById($id, $columns = ['*'])
    {
        $result = $this->getQuery(true)->find($id, $columns);
        //记录sql
        $this->setLastSql();
        if ($result instanceof Model) {
            return $result->toArray();
        }
        return [];
    }

    /**
     * 根据字段获取一行数据
     *
     * @param       $column
     * @param       $value
     * @param array $columns
     *
     * @return array
     */
    public function findBy($column, $value, $columns = ['*'])
    {
        $result = $this->getQuery(true)->where($column, $value)->first($columns);
        //记录sql
        $this->setLastSql();
        if ($result instanceof Model) {
            return $result->toArray();
        }
        return [];
    }

    /**
     * 获取单个字段
     *
     * @param $column
     *
     * @return string
     */
    public function value($column)
    {
        $value = $this->getQuery()->value($column);
        //记录sql
        $this->setLastSql();
        return $value;
    }

    /**
     * 获取单列
     *
     * @param $column
     *
     * @return array
     */
    public function findCol($column)
    {
        $result = $this->all([$column]);
        return array_column($result, $column);
    }

    /**
     * 获取列表
     *
     * @param array $columns
     *
     * @return array
     */
    public function all($columns = ['*'])
    {
        $result = $this->getQuery()->get($columns);
        //记录sql
        $this->setLastSql();
        if ($result) {
            return $result->toArray();
        }
        return [];
    }

    /**
     * where语句
     *
     * @param        $column
     * @param null   $operator
     * @param null   $value
     * @param string $boolean
     *
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->getQuery()->where(...func_get_args());
        return $this;
    }

    /**
     * orWhere语句
     *
     * @param        $column
     * @param null   $operator
     * @param null   $value
     * @param string $boolean
     *
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        $this->getQuery()->orWhere(...func_get_args());
        return $this;
    }

    /**
     * where字符串
     *
     * @param        $sql
     * @param array  $bindings
     * @param string $boolean
     *
     * @return $this
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        $this->getQuery()->whereRaw(...func_get_args());
        return $this;
    }

    /**
     * whereIn or not in
     *
     * @param        $column
     * @param        $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $this->getQuery()->whereIn(...func_get_args());
        return $this;
    }

    /**
     * whereBetween
     *
     * @param        $column
     * @param array  $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return $this
     */
    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $this->getQuery()->whereBetween(...func_get_args());
        return $this;
    }

    /**
     * 处理order
     *
     * @param        $column
     * @param string $direction
     *
     * @return $this
     */
    public function order($column, $direction = 'asc')
    {
        $orders = is_array($column) ? $column : [$column => $direction];
        foreach ($orders as $key => $sort) {
            $this->getQuery()->orderBy($key, $sort);
        }
        return $this;
    }

    /**
     * 处理Group
     *
     * @param ...$groups
     *
     * @return $this
     */
    public function group(...$groups)
    {
        $this->getQuery()->groupBy(...$groups);
        return $this;
    }

    /**
     * 处理offset
     *
     * @param $value
     *
     * @return $this
     */
    public function offset($value)
    {
        $this->getQuery()->offset($value);
        return $this;
    }

    /**
     * 处理limit
     *
     * @param $value
     *
     * @return $this
     */
    public function limit($value)
    {
        $this->getQuery()->limit($value);
        return $this;
    }

    /**
     * 处理forPage，翻页查询
     *
     * @param $page     当前页数
     * @param $perPage  每页查询数据
     *
     * @return $this
     */
    public function forPage($page, $perPage)
    {
        $this->getQuery()->forPage($page, $perPage);
        return $this;
    }

    /**
     * 创建
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data)
    {
        try {
            $r = $this->getQuery(true)->insertGetId($data);
        } catch (\Exception $e) {
            \Utils::mLog('SqlException/' . date("Ymd") . '.log', $e->getMessage(), $data);
            $r = false;
        }
        //记录sql
        $this->setLastSql();
        return $r;
    }

    /**
     * 批量创建
     *
     * @param array $data
     *
     * @return bool
     */
    public function createAll(array $data)
    {
        try {
            $r = $this->getQuery(true)->insert($data);
        } catch (\Exception $e) {
            \Utils::mLog('SqlException/' . date("Ymd") . '.log', $e->getMessage(), $data);
            $r = false;
        }
        //记录sql
        $this->setLastSql();
        return $r;
    }

    /**
     * 增加
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        try {
            $r = $this->getQuery()->increment(...func_get_args());
        } catch (\Exception $e) {
            \Utils::mLog('SqlException/' . date("Ymd") . '.log', $e->getMessage(), func_get_args());
            $r = false;
        }
        //记录sql
        $this->setLastSql();
        return $r;
    }

    /**
     * 减少
     */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        try {
            $r = $this->getQuery()->decrement(...func_get_args());
        } catch (\Exception $e) {
            \Utils::mLog('SqlException/' . date("Ymd") . '.log', $e->getMessage(), func_get_args());
            $r = false;
        }
        //记录sql
        $this->setLastSql();
        return $r;
    }

    /**
     * 修改
     *
     * @param array $data
     *
     * @return bool
     */
    public function update(array $data)
    {
        try {
            $r                  = $this->getQuery()->update($data);
            $this->affectedRows = $r;
        } catch (\Exception $e) {
            \Utils::mLog('SqlException/' . date("Ymd") . '.log', $e->getMessage(), $data);
            $r = false;
        }
        //记录sql
        $this->setLastSql();
        return is_numeric($r) ? true : $r;
    }

    /**
     * 删除记录
     *
     * @return bool
     */
    public function delete()
    {
        try {
            $r = $this->getQuery()->delete();
        } catch (\Exception $e) {
            \Utils::mLog('SqlException/' . date("Ymd") . '.log', $e->getMessage());
            $r = false;
        }
        //记录sql
        $this->setLastSql();
        return $r;
    }

    /**
     * 分页
     *
     * @param int   $size
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $p = $this->getQuery()->paginate(...func_get_args());
        //记录sql
        $this->setLastSql();
        return $p;
    }

    /**
     * 返回总数
     *
     * @param string $columns
     *
     * @return int
     */
    public function count($columns = '*')
    {
        $c = $this->getQuery()->count($columns);
        //记录sql
        $this->setLastSql();
        return $c;
    }

    /**
     * 返回最大值
     *
     * @param string $column
     *
     * @return mixed
     */
    public function max($column)
    {
        $c = $this->getQuery()->max($column);
        $this->setLastSql();
        return $c;
    }

    /**
     * 返回最小值
     *
     * @param $column
     *
     * @return mixed
     */
    public function min($column)
    {
        $c = $this->getQuery()->min($column);
        $this->setLastSql();
        return $c;
    }

    /**
     * 返回字段之和
     *
     * @param $column
     *
     * @return mixed
     */
    public function sum($column)
    {
        $c = $this->getQuery()->sum($column);
        //记录sql
        $this->setLastSql();
        return $c;
    }

    /**
     * 获取查询器
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getQuery($new = false)
    {
        if (is_null($this->query) || $new) {
            $this->query = $this->model->newQuery();
        }
        return $this->query;
    }

    /**
     * 设置查询器
     *
     * @param null $query
     *
     * @return $this
     */
    public function setQuery($query = null)
    {
        $this->query = $query;
        return $this;
    }

    public function holdQuery($holdQuery = true)
    {
        $this->holdQuery = $holdQuery;
        return $this;
    }

    /**
     * 记录最后一次sql
     */
    protected function setLastSql()
    {
        if ($this->query) {
            $this->lastSql = $this->query->toSql();
            if (!$this->holdQuery) {
                $this->query = null;
            }
        } else {
            $this->lastSql = $this->model->toSql();
        }
    }

    /**
     * 获取当前连接
     *
     * @return \Illuminate\Database\Connection
     */
    public function on()
    {
        return $this->model->getConnection();
    }

    /**
     * 开启事务
     */
    public function beginTransaction()
    {
        $this->on()->beginTransaction();
    }

    /**
     * 提交事务
     */
    public function commit()
    {
        return $this->on()->commit();
    }

    /**
     * 回滚事务
     */
    public function rollBack()
    {
        return $this->on()->rollBack();
    }

    /**
     * 查询加锁
     *
     * @param bool $lock true:更新锁 false:共享锁
     *
     * @return $this
     */
    public function lock($lock = false)
    {
        $this->model->lock($lock);
        return $this;
    }

    /**
     * 返回sql
     *
     * @return string
     */
    public function getSql()
    {
        return $this->lastSql;
    }

    protected function returnFormat($code, $message = '', $data = [])
    {
        return ['code' => $code, 'info' => $message, 'data' => $data];
    }

    /**
     * 强制切到写库
     */
    public function onWriteConnection()
    {
        $this->setQuery($this->model->onWriteConnection());
        return $this;
    }

    /**
     * 获取生效条目
     *
     * @return int
     */
    public function getAffectedRows()
    {
        return $this->affectedRows;
    }

    public function orderByRaw($sql, $bindings = [])
    {
        $this->getQuery()->orderByRaw(...func_get_args());
        return $this;
    }
}
