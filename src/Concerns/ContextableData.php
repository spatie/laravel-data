<?php

namespace Spatie\LaravelData\Concerns;

use Spatie\LaravelData\Contracts\IncludeableData as IncludeableDataContract;
use Spatie\LaravelData\Contracts\WrappableData as WrappableDataContract;
use Spatie\LaravelData\Support\Partials\PartialsDefinition;
use Spatie\LaravelData\Support\Transformation\DataContext;
use Spatie\LaravelData\Support\Wrapping\Wrap;
use Spatie\LaravelData\Support\Wrapping\WrapType;

trait ContextableData
{
    protected ?DataContext $_dataContext = null;

    public function getDataContext(): DataContext
    {
        if ($this->_dataContext === null) {
            $wrap = match (true) {
                method_exists($this, 'defaultWrap') => new Wrap(WrapType::Defined, $this->defaultWrap()),
                default => new Wrap(WrapType::UseGlobal),
            };

            return $this->_dataContext = new DataContext(
                new PartialsDefinition(
                    $this instanceof IncludeableDataContract ? $this->includeProperties() : [],
                    $this instanceof IncludeableDataContract ? $this->excludeProperties() : [],
                    $this instanceof IncludeableDataContract ? $this->onlyProperties() : [],
                    $this instanceof IncludeableDataContract ? $this->exceptProperties() : [],
                ),
                $this instanceof WrappableDataContract ? $wrap : new Wrap(WrapType::UseGlobal),
            );
        }

        return $this->_dataContext;
    }
}
