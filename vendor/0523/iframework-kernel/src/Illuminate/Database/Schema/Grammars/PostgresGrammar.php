<?php

namespace Illuminate\Database\Schema\Grammars;

use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;

class PostgresGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     *
     * @var array
     */
    protected $modifiers = ['Increment', 'Nullable', 'Default'];

    /**
     * The columns available as serials.
     *
     * @var array
     */
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];

    /**
     * Compile the query to determine if a table exists.
     *
     * @return string
     */
    public function compileTableExists()
    {
        return 'select * from information_schema.tables where table_name = ?';
    }

    /**
     * Compile the query to determine the list of columns.
     *
     * @param  string  $table
     * @return string
     */
    public function compileColumnExists($table)
    {
        return "select column_name from information_schema.columns where table_name = '$table'";
    }

    /**
     * Compile a create table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileCreate(Blueprint $blueprint, Fluent $command)
    {
        $columns = implode(', ', $this->getColumns($blueprint));

        $sql = $blueprint->temporary ? 'create temporary' : 'create';

        $sql .= ' table '.$this->wrapTable($blueprint)." ($columns)";

        return $sql;
    }

    /**
     * Compile a create table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->prefixArray('add column', $this->getColumns($blueprint));

        return 'alter table '.$table.' '.implode(', ', $columns);
    }

    /**
     * Compile a primary key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compilePrimary(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->columnize($command->columns);

        /**
         * uint
         * 设置主键名称
         * 填充率固定为100
         */
        if (true) {
            $pkName = 'pk_' . str_replace('.', '_', $blueprint->getTable());
            return 'alter table '.$this->wrapTable($blueprint)." add CONSTRAINT {$pkName} primary key ({$columns}) WITH (FILLFACTOR=100)";
        } else {
            return 'alter table '.$this->wrapTable($blueprint)." add primary key ({$columns})";
        }
    }

    /**
     * Compile a unique key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        if (true) {
            $ukName = 'unique_' . str_replace(['"', "'"], null, $columns) . '___' . str_replace('.', '_', $blueprint->getTable());
            return "CREATE UNIQUE INDEX $ukName ON $table ($columns) WITH (FILLFACTOR=70)";
        } else {
            $index = $this->wrap($command->index);
            return "alter table $table add constraint {$index} unique ($columns)";
        }
    }

    /**
     * Compile a plain index key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        if (true) {
            $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '___' . str_replace('.', '_', $blueprint->getTable());
            return "CREATE INDEX $ukName ON $table ($columns) WITH (FILLFACTOR=70)";
        } else {
            $index = $this->wrap($command->index);

            return "create index {$index} on ".$this->wrapTable($blueprint)." ({$columns})";
        }
    }

    /**
     * 全小写索引
     * 解决PostgreSQL大小写敏感问题
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexStringLower(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        if (true) {
            $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '_lower___' . str_replace('.', '_', $blueprint->getTable());
            return "CREATE INDEX $ukName ON $table (lower($columns)) WITH (FILLFACTOR=70)";
        } else {
            return "create index {$command->index} on ".$this->wrapTable($blueprint)." ({$columns})";
        }
    }

    /**
     * gin索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexGin(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        if (true) {
            $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '___' . str_replace('.', '_', $blueprint->getTable());
            return "CREATE INDEX $ukName ON $table USING gin ($columns)";
        } else {
            return "create index {$command->index} on ".$this->wrapTable($blueprint)." ({$columns})";
        }
    }

    /**
     * cn_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexCnClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__cn_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'cn_clean' as text))";
    }

    /**
     * en_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexEnClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__en_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'en_clean' as text))";
    }

    /**
     * ja_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexJaClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__ja_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'ja_clean' as text))";
    }

    /**
     * ko_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexKoClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__ko_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'ko_clean' as text))";
    }

    /**
     * de_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexDeClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__de_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'de_clean' as text))";
    }

    /**
     * fr_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexFrClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__fr_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'fr_clean' as text))";
    }

    /**
     * ru_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexRuClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__ru_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'ru_clean' as text))";
    }

    /**
     * es_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexEsClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__es_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'es_clean' as text))";
    }

    /**
     * it_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexItClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__it_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'it_clean' as text))";
    }

    /**
     * pt_clean 索引
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileIndexPtClean(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $columns = $this->columnize($command->columns);

        $ukName = 'index_' . str_replace(['"', "'"], null, $columns) . '__pt_clean___' . str_replace('.', '_', $blueprint->getTable());
        return "CREATE INDEX $ukName ON $table USING btree (cast ($columns->>'pt_clean' as text))";
    }

    /**
     * Compile a drop table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table '.$this->wrapTable($blueprint);
    }

    /**
     * Compile a drop table (if exists) command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        /**
         * 增加 CASCADE , 避免有外键等影响删除不掉
         */
        return 'drop table if exists '.$this->wrapTable($blueprint) . ' CASCADE';
    }

    /**
     * Compile a drop column command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDropColumn(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('drop column', $this->wrapArray($command->columns));

        $table = $this->wrapTable($blueprint);

        return 'alter table '.$table.' '.implode(', ', $columns);
    }

    /**
     * Compile a drop primary key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
    {
        $table = $blueprint->getTable();

        $index = $this->wrap("{$table}_pkey");

        return 'alter table '.$this->wrapTable($blueprint)." drop constraint {$index}";
    }

    /**
     * Compile a drop unique key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $index = $this->wrap($command->index);

        return "alter table {$table} drop constraint {$index}";
    }

    /**
     * Compile a drop index command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);

        return "drop index {$index}";
    }

    /**
     * Compile a drop foreign key command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileDropForeign(Blueprint $blueprint, Fluent $command)
    {
        $table = $this->wrapTable($blueprint);

        $index = $this->wrap($command->index);

        return "alter table {$table} drop constraint {$index}";
    }

    /**
     * Compile a rename table command.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $command
     * @return string
     */
    public function compileRename(Blueprint $blueprint, Fluent $command)
    {
        $from = $this->wrapTable($blueprint);

        return "alter table {$from} rename to ".$this->wrapTable($command->to);
    }

    /**
     * Create the column definition for a char type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeChar(Fluent $column)
    {
        return "char({$column->length})";
    }

    /**
     * Create the column definition for a string type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeString(Fluent $column)
    {
        return "varchar({$column->length})";
    }

    /**
     * Create the column definition for a text type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeText(Fluent $column)
    {
        return 'text';
    }

    /**
     * 创建 tsvector 类型字段
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTsvector(Fluent $column)
    {
        return 'tsvector';
    }

    /**
     * 创建 dateDiff 类型字段
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDateDiff(Fluent $column)
    {
        return 'interval second';
    }

    /**
     * 创建 intarr 类型字段
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeIntarr(Fluent $column)
    {
        return 'bigint[]';
    }

    /**
     * 创建 textarr 类型字段
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTextarr(Fluent $column)
    {
        return 'text[]';
    }

    /**
     * 创建 ip 类型字段
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeIp(Fluent $column)
    {
        return 'cidr';
    }

    /**
     * Create the column definition for a medium text type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMediumText(Fluent $column)
    {
        return 'text';
    }

    /**
     * Create the column definition for a long text type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeLongText(Fluent $column)
    {
        return 'text';
    }

    /**
     * Create the column definition for a integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeInteger(Fluent $column)
    {
        return $column->autoIncrement ? 'serial' : 'integer';
    }

    /**
     * Create the column definition for a big integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeBigInteger(Fluent $column)
    {
        return $column->autoIncrement ? 'bigserial' : 'bigint';
    }

    /**
     * Create the column definition for a medium integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeMediumInteger(Fluent $column)
    {
        return $column->autoIncrement ? 'serial' : 'integer';
    }

    /**
     * Create the column definition for a tiny integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTinyInteger(Fluent $column)
    {
        return $column->autoIncrement ? 'smallserial' : 'smallint';
    }

    /**
     * Create the column definition for a small integer type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeSmallInteger(Fluent $column)
    {
        return $column->autoIncrement ? 'smallserial' : 'smallint';
    }

    /**
     * Create the column definition for a float type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeFloat(Fluent $column)
    {
        return $this->typeDouble($column);
    }

    /**
     * Create the column definition for a double type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDouble(Fluent $column)
    {
        return 'double precision';
    }

    /**
     * Create the column definition for a decimal type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDecimal(Fluent $column)
    {
        return "decimal({$column->total}, {$column->places})";
    }

    /**
     * Create the column definition for a boolean type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeBoolean(Fluent $column)
    {
        return 'boolean';
    }

    /**
     * Create the column definition for an enum type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeEnum(Fluent $column)
    {
        $allowed = array_map(function ($a) {
            return "'".$a."'";
        }, $column->allowed);

        return "varchar(255) check (\"{$column->name}\" in (".implode(', ', $allowed).'))';
    }

    /**
     * Create the column definition for a json type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeJson(Fluent $column)
    {
        return 'json';
    }

    /**
     * Create the column definition for a jsonb type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeJsonb(Fluent $column)
    {
        return 'jsonb';
    }

    /**
     * Create the column definition for a date type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDate(Fluent $column)
    {
        return 'date';
    }

    /**
     * Create the column definition for a date-time type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDateTime(Fluent $column)
    {
        return 'timestamp(0) without time zone';
    }

    /**
     * Create the column definition for a date-time type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeDateTimeTz(Fluent $column)
    {
        return 'timestamp(0) with time zone';
    }

    /**
     * Create the column definition for a time type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTime(Fluent $column)
    {
        return 'time(0) without time zone';
    }

    /**
     * Create the column definition for a time type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTimeTz(Fluent $column)
    {
        return 'time(0) with time zone';
    }

    /**
     * Create the column definition for a timestamp type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTimestamp(Fluent $column)
    {
        if ($column->useCurrent) {
            return 'timestamp(0) without time zone default CURRENT_TIMESTAMP(0)';
        }

        return 'timestamp(0) without time zone';
    }

    /**
     * Create the column definition for a timestamp type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeTimestampTz(Fluent $column)
    {
        if ($column->useCurrent) {
            return 'timestamp(0) with time zone default CURRENT_TIMESTAMP(0)';
        }

        return 'timestamp(0) with time zone';
    }

    /**
     * Create the column definition for a binary type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeBinary(Fluent $column)
    {
        return 'bytea';
    }

    /**
     * Create the column definition for a uuid type.
     *
     * @param  \Illuminate\Support\Fluent  $column
     * @return string
     */
    protected function typeUuid(Fluent $column)
    {
        return 'uuid';
    }

    /**
     * Get the SQL for a nullable column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $column
     * @return string|null
     */
    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {
        return $column->nullable ? ' null' : ' not null';
    }

    /**
     * Get the SQL for a default column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $column
     * @return string|null
     */
    protected function modifyDefault(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->default)) {
            return ' default '.$this->getDefaultValue($column->default);
        }
    }

    /**
     * Get the SQL for an auto-increment column modifier.
     *
     * @param  \Illuminate\Database\Schema\Blueprint  $blueprint
     * @param  \Illuminate\Support\Fluent  $column
     * @return string|null
     */
    protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' primary key';
        }
    }
}
