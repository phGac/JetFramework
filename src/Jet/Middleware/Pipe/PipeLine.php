<?php


namespace Jet\Middleware\Pipe;


use http\Exception\InvalidArgumentException;

class PipeLine
{
    private $stages;

    function __construct(array $stages = [])
    {
        foreach ($stages as $stage) {
            if(! is_callable($stage)) throw new InvalidArgumentException();
        }

        $this->stages = $stages;
    }

    public function pipe(callable $stage)
    {
        $stages = $this->stages;
        $stages[] = $stage;

        return new static($stages);
    }

    /**
     * @param mixed $original
     * @return mixed
     */
    public function process($original)
    {
        $stages = [];
        for ($i = 0; $i < count($this->stages); $i++) {
            $next = (isset($this->stages[($i + 1)])) ? $this->stages[($i + 1)] : null;
            $next = function($value) use ($next) {
                if(is_callable($next)) call_user_func($next, $value);
            };
            $stages = [$this->stages[$i], $next];
        }

        $returned = $original;
        foreach($stages as $stage) {
            $returned = call_user_func($stage[0], $returned, $stage[1]);
        }
        return $returned;
    }
}