<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ECSPrefix202301\Symfony\Component\Console\Output;

use ECSPrefix202301\Symfony\Component\Console\Formatter\NullOutputFormatter;
use ECSPrefix202301\Symfony\Component\Console\Formatter\OutputFormatterInterface;
/**
 * NullOutput suppresses all output.
 *
 *     $output = new NullOutput();
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Tobias Schultze <http://tobion.de>
 */
class NullOutput implements OutputInterface
{
    /**
     * @var \ECSPrefix202301\Symfony\Component\Console\Formatter\NullOutputFormatter
     */
    private $formatter;
    public function setFormatter(OutputFormatterInterface $formatter)
    {
        // do nothing
    }
    public function getFormatter() : OutputFormatterInterface
    {
        // to comply with the interface we must return a OutputFormatterInterface
        return $this->formatter = $this->formatter ?? new NullOutputFormatter();
    }
    public function setDecorated(bool $decorated)
    {
        // do nothing
    }
    public function isDecorated() : bool
    {
        return \false;
    }
    public function setVerbosity(int $level)
    {
        // do nothing
    }
    public function getVerbosity() : int
    {
        return self::VERBOSITY_QUIET;
    }
    public function isQuiet() : bool
    {
        return \true;
    }
    public function isVerbose() : bool
    {
        return \false;
    }
    public function isVeryVerbose() : bool
    {
        return \false;
    }
    public function isDebug() : bool
    {
        return \false;
    }
    /**
     * @param string|mixed[] $messages
     */
    public function writeln($messages, int $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
    /**
     * @param string|mixed[] $messages
     */
    public function write($messages, bool $newline = \false, int $options = self::OUTPUT_NORMAL)
    {
        // do nothing
    }
}
