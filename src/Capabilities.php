<?php

namespace Foolz\SphinxQL;

/**
 * Describes detected engine/runtime capabilities.
 */
class Capabilities
{
    /**
     * @var string
     */
    private $engine;

    /**
     * @var string
     */
    private $version;

    /**
     * @var array<string,bool>
     */
    private $features;

    /**
     * @param string            $engine
     * @param string            $version
     * @param array<string,bool> $features
     */
    public function __construct($engine, $version, array $features)
    {
        $this->engine = strtoupper((string) $engine);
        $this->version = (string) $version;
        $this->features = $features;
    }

    /**
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return array<string,bool>
     */
    public function getFeatures()
    {
        return $this->features;
    }

    /**
     * @param string $feature
     *
     * @return bool
     */
    public function supports($feature)
    {
        return !empty($this->features[$feature]);
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray()
    {
        return array(
            'engine' => $this->engine,
            'version' => $this->version,
            'features' => $this->features,
        );
    }
}

