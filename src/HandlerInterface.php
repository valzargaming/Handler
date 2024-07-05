<?php

/*
 * This file is a part of the Civ13 project.
 *
 * Copyright (c) 2024-present Valithor Obsidion <valithor@valzargaming.com>
 */

namespace Handler;

use \Traversable;

interface HandlerInterface
{
    //public function __construct(array $attributes = []);

    // Basic CRUD Operations for Attributes
    public function get(string $name): mixed;
    public function set(string $name, mixed $value): static;
    public function __push(null|int|string $name = null, mixed $value): static;
    public function __pushItems(null|int|string $name = null, mixed ...$items): static;
    public function __pull(int|string $name, mixed $default = null): mixed;
    public function __fill(array $values): static;
    public function __clear(): static;

    // Count and Access
    public function __count(null|int|string $name = null): int;
    public function __first(null|int|string $name = null): mixed;
    public function __last(null|int|string $name = null): mixed;

    // Existence Checks
    public function __isset(int|string $name): bool;
    public function __has(int|string $name, array ...$offsets): bool;

    // Search and Filter
    public function __find(int|string $name, callable $callback): mixed;
    public function __filter(int|string $name, callable $callback): static;
    public function __map(int|string $name, callable $callback): static;

    // Merge and Offset Operations
    public function __merge(array|object $data): static;
    public function __offsetExists(int|string $name): bool;
    public function __offsetGet(int|string $offset): mixed;
    public function __offsetSet(int|string $name, mixed $value): static;
    public function __offsetSets(array $names, mixed $value): static;
    public function __offsetUnset(int|string $name): static;
    public function __offsetUnsets(array $names): static;
    public function __getOffset(int|string $name, callable $callback): int|string|false;
    public function __setOffset(int|string $name, callable $callback): static;

    // Handler Operations
    public function __getHandler(): ?callable;
    public function __pushHandler(callable $callback, null|int|string $offset = null): static;
    public function __pushHandlers(array $handlers): static;
    public function __pullHandler(null|int|string $offset = null, mixed $default = null): mixed;
    public function __fillHandlers(array $items): static;
    public function __clearHandlers(): static;

    // Iterator and Conversion
    public function __getIterator(): Traversable;
    public function __toArray(): array;

    // Debugging
    public function __debugInfo(): array;
}