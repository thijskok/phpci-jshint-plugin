<?php

namespace ThijsKok\PHPCI\Plugin;

use PHPCI;
use PHPCI\Builder;
use PHPCI\Model\Build;

/**
 * PHPCI JSHint Plugin - Allows JSHint testing.
 * @author Thijs Kok <mail@thijskok.nl>
 * @package PHPCI
 * @subpackage Plugins
 */
class JSHint implements PHPCI\Plugin, PHPCI\ZeroConfigPlugin
{
    /**
     * @var \PHPCI\Builder
     */
    protected $phpci;

    /**
     * @var \PHPCI\Model\Build
     */
    protected $build;

    /**
     * @var string, based on the assumption the root may not hold the code to be
     * tested, extends the base path only if the provided path is relative. Absolute
     * paths are used verbatim
     */
    protected $path;

    /**
     * @var int, the number of allowed warnings
     */
    protected $allowed_warnings;

    /**
     * Check if this plugin can be executed.
     * @param $stage
     * @param Builder $builder
     * @param Build $build
     * @return bool
     */
    public static function canExecute($stage, Builder $builder, Build $build)
    {
        if ($stage == 'test') {
            return true;
        }

        return false;
    }

    /**
     * Standard Constructor
     *
     * $options['path']  JS Directory. Default: %BUILDPATH%
     * $options['stub']  Stub Content. No Default Value
     *
     * @param Builder $phpci
     * @param Build   $build
     * @param array   $options
     */
    public function __construct(Builder $phpci, Build $build, array $options = array())
    {
        $this->phpci = $phpci;
        $this->build = $build;
        $this->path = '';
        $this->allowed_warnings = 0;

        if (!empty($options['path'])) {
            $this->path = $options['path'];
        }

        if (array_key_exists('allowed_warnings', $options)) {
            $this->allowed_warnings = (int)$options['allowed_warnings'];
        }
    }

    /**
     * Runs PHP Mess Detector in a specified directory.
     */
    public function execute()
    {
        $jshintBinaryPath = $this->phpci->findBinary('jshint');

        $this->executeJsHint($jshintBinaryPath);

        list($errorCount, $data) = $this->processReport(trim($this->phpci->getLastOutput()));
        $this->build->storeMeta('jshint-warnings', $errorCount);
        $this->build->storeMeta('jshint-data', $data);

        return $this->wasLastExecSuccessful($errorCount);
    }

    /**
     * Process JSHint's XML output report.
     * @param $xmlString
     * @return array
     * @throws \Exception
     */
    protected function processReport($xmlString)
    {
        $xml = simplexml_load_string($xmlString);

        if ($xml === false) {
            $this->phpci->log($xmlString);
            throw new \Exception('Could not process JSHint report XML.');
        }

        $warnings = 0;
        $data = array();

        foreach ($xml->file as $file) {
            $fileName = (string)$file['name'];
            $fileName = str_replace($this->phpci->buildPath, '', $fileName);

            foreach ($file->error as $error) {
                $warnings++;
                $warning = array(
                    'file' => $fileName,
                    'line' => (int)$error['line'],
                    'severity' => (string)$error['severity'],
                    'message' => (string)$error['message'],
                );

                $this->build->reportError($this->phpci, $fileName, (int)$error['line'], (string)$error['message']);
                $data[] = $warning;
            }
        }

        return array($warnings, $data);
    }

    /**
     * Execute JSHint.
     * @param $binaryPath
     */
    protected function executeJsHint($binaryPath)
    {
        $cmd = $binaryPath . ' %s --reporter checkstyle';

        $path = $this->getTargetPath();

        // Disable exec output logging, as we don't want the XML report in the log:
        $this->phpci->logExecOutput(false);

        // Run JSHint:
        $this->phpci->executeCommand(
            $cmd,
            $path
        );

        // Re-enable exec output logging:
        $this->phpci->logExecOutput(true);
    }

    /**
     * Get the path JSHint should be run against.
     * @return string
     */
    protected function getTargetPath()
    {
        $path = $this->phpci->buildPath . $this->path;
        if (!empty($this->path) && $this->path{0} == '/') {
            $path = $this->path;
            return $path;
        }
        return $path;
    }

    /**
     * Returns a boolean indicating if the error count can be considered a success.
     *
     * @param int $errorCount
     * @return bool
     */
    protected function wasLastExecSuccessful($errorCount)
    {
        $success = true;

        if ($this->allowed_warnings != -1 && $errorCount > $this->allowed_warnings) {
            $success = false;
            return $success;
        }
        return $success;
    }
}