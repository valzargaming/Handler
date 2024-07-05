<?php

/*
 * This file is a part of the Civ13 project.
 *
 * Copyright (c) 2024-present Valithor Obsidion <valithor@valzargaming.com>
 */

namespace Handler;

use \ArrayIterator;
use \Traversable;

trait HandlerTrait
{
    protected array $fillable = ['handlers'];
    protected array $attributes = [
        'handlers' => [], // array of callables
    ];

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $name => $value) {
            if (array_key_exists($name, $this->fillable)) {
                $this->setAttribute($name, $value);
            }
        }
    }

    /**
     * Retrieves the value of the specified attribute.
     *
     * @param string $key The key of the attribute to retrieve.
     * @return mixed|null The value of the attribute if it exists, null otherwise.
     */
    public function get(string $name): mixed
    {
        if (! isset($this->attributes[$name])) return null;
        
        return $this->attributes[$name];
    }

    /**
     * Sets the value of the specified attribute.
     *
     * @param string $key The key of the attribute.
     * @param mixed|null $value The value to be set.
     * @throws \InvalidArgumentException If the key is not valid.
     * @return void
     */
    public function set(null|int|string $name = null, mixed $value): static
    {
        if (! in_array($name, $this->fillable)) throw new \InvalidArgumentException('Handler::setAttribute() expects parameter 1 to be a valid key, ' . $name . ' given');
        $this->attributes[$name] = $value;

        return $this;
    }

    public function __push(null|int|string $name = null, mixed $value): static
    {
        if (null === $name) {
            $this->attributes[] = $value;

            return $this;
        }

        if (! isset($this->fillable[$name])) throw new \InvalidArgumentException('Handler::offsetSet() expects parameter 1 to be a valid key, ' . $name . ' given');

        if (! isset($this->attributes[$name]) || is_array($this->attributes[$name]) || $this->attributes[$name] instanceof \ArrayAccess) {
            $this->attributes[$name][] = $value;
        } else throw new \InvalidArgumentException('Handler::pushAttribute() expects parameter 1 to be an array or an object implementing ArrayAccess, ' . gettype($this->attributes[$name]) . ' given');

        return $this;
    }

    public function __pushItems(null|int|string $name = null, mixed ...$items): static
    {
        foreach ($items as $item) $this->pushAttribute($name, $item);

        return $this;
    }

    public function __pull(int|string $name, mixed $default = null): mixed
    {
        if (isset($this->attributes[$name])) {
            $item = $this->attributes[$name];
            unset($this->attributes[$name]);
            return $item;
        }
        
        return $default;
    }

    public function __fill(array $values): static
    {
        foreach ($values as $name => $value)
            if (array_key_exists($name, $this->fillable))
                $this->setAttribute($name, $value);
        
        return $this;
    }

    public function __clear(): static
    {
        $this->attributes = [];

        return $this;
    }

    public function __count(null|int|string $name = null): int
    {
        if ($name === null) return count($this->attributes);

        return count($this->attributes[$name]);
    }

    public function __first(null|int|string $name = null): mixed
    {
        if ($name === null) return [array_shift($this->toArray())];

        if (! isset($this->attributes[$name])) return null;

        return array_shift($this->toArray()[$name]);
    }

    public function __last(null|int|string $name = null): mixed
    {
        if ($name === null) return [array_pop($this->toArray())];

        if (! isset($this->attributes[$name])) return null;

        return array_pop($this->toArray()[$name]);
    }

    public function __isset(int|string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function __has(int|string $name, array ...$offsets): bool
    {
        foreach ($offsets as $offset)
            if (! isset($this->attributes[$name][$offset]))
                return false;
        return true;
    }

    public function __find(int|string $name, callable $callback): mixed
    {
        foreach ($this->$name as $value)
            if ($callback($value))
                return $value;
        return null;
    }

    public function __filter(int|string $name, callable $callback): static
    {
        foreach ($this->$name as $offset => $value)
            if (! $callback($value))
                $this->pull($name, $offset);
        return $this;
    }

    public function __map(int|string $name, callable $callback): static
    {
        foreach ($this->$name as $offset => $value)
            $this->attributes[$name][$offset] = $callback($value);
        return $this;
    }

    /**
     * @throws InvalidArgumentException if toArray property does not exist
     */
    public function __merge(array|object $data): static
    {
        if (is_object($data) && ! property_exists($data, 'toArray')) {
            throw new \InvalidArgumentException('Handler::merge() expects parameter 1 to be an object with a method named "toArray", ' . gettype($data) . ' given');
            return $this;
        }

        if (is_object($data)) $data = $data->toArray();

        $this->attributes = array_merge($this->attributes, array_shift($data));

        return $this;
    }

    public function __offsetExists(int|string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function __offsetGet(int|string $offset): mixed
    {
        if (! isset($this->attributes[$offset])) return null;
        
        return $this->attributes[$offset];    
    }

    public function __offsetSet(int|string $name, mixed $value): static
    {
        if (! isset($this->fillable[$name])) throw new \InvalidArgumentException('Handler::offsetSet() expects parameter 1 to be a valid key, ' . $name . ' given');
        
        $this->attributes[$name] = $value;

        return $this;
    }

    public function __offsetSets(array $names, mixed $value): static
    {
        foreach ($names as $name) {
            if (! isset($this->fillable[$name])) throw new \InvalidArgumentException('Handler::offsetSet() expects parameter 1 to be a valid key, ' . $name . ' given');

            $this->attributes[$name] = $value;
        }

        return $this;        
    }

    public function __offsetUnset(int|string $name): static
    {
        unset($this->attributes[$name]);

        return $this;
    }

    public function __offsetUnsets(array $names): static
    {
        foreach ($names as $name) unset($this->attributes[$name]);

        return $this;
    }

    public function __getOffset(int|string $name, callable $callback): int|string|false
    {
        if (is_array($this->$name) || $this->$name instanceof \ArrayAccess) return array_search($callback, $this->attributes[$name]);

        return false;
    }

    public function __setOffset(int|string $name, callable $callback): static
    {
        if (! isset($this->fillable[$name])) throw new \InvalidArgumentException('Handler::offsetSet() expects parameter 1 to be a valid key, ' . $name . ' given');

        while ($offset = $this->getOffset($name, $callback) !== false) unset($this->attributes[$offset]);

        $this->atrributes[$name] = $callback;

        return $this;
    }

    public function __getHandler(int|string $offset = null): ?callable
    {
        if (! isset($this->attributes['handler'][$offset])) return null;

        return $this->attributes['handler'][$offset];
        
    }

    public function __pushHandler(callable $callback, null|int|string $offset = null): static
    {
        if (null === $offset) $this->attributes['handler'][] = $callback;
        else $this->attributes['handler'][$offset] = $callback;

        return $this;
    }

    public function __pushHandlers(array $handlers): static
    {
        foreach ($handlers as $offset => $callback) $this->pushHandler($callback, $offset);

        return $this;
    }

    public function __pullHandler(null|int|string $offset = null, mixed $default = null): mixed
    {
        if (isset($this->attributes['handler'][$offset])) {
            $item = $this->attributes['handler'][$offset];
            unset($this->attributes['handler'][$offset]);
            return $item;
        }
        
        return $default;
    }

    public function __fillHandlers(array $items): static
    {
        $this->attributes['handlers'] = $items;

        return $this;
    }

    public function __clearHandlers(): static
    {
        $this->attributes['handlers'] = [];

        return $this;
    }

    public function __getIterator(): Traversable
    {
        return new ArrayIterator($this->attributes);
    }

    public function __toArray(): array
    {
        return $this->attributes;
    }

    public function ____debugInfo(): array
    {
        return ['attributes' => array_keys($this->attributes)];
    }
}