<?php

namespace Testes\Coverage;
use Arrayiterator;
use Closure;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use SplFileObject;
use UnexpectedValueException;

class Analyzer implements Countable, IteratorAggregate
{
	private $result;

	private $files;

	public function __construct(CoverageResult $result)
	{
    	$this->files  = new ArrayIterator;
		$this->result = $result;
	}
	
	public function count()
	{
    	return $this->files->count();
	}
	
	public function getIterator()
	{
	    return $this->files;
	}

	public function addFile($file)
	{
	    $this->files->offsetSet(null, new File($file, $this->result));
		return $this;
	}
	
	public function removeFile($file)
	{
    	if (!is_file($file)) {
        	throw new UnexpectedValueException('Unable to remove the file because "' . $file . '" is not a file.');
    	}
    	
    	$real = realpath($file);
    	foreach ($this->files as $index => $file) {
        	if ($file->__toString() === $real) {
            	$this->files->offsetUnset($index);
            	break;
        	}
    	}
    	
    	return $this;
	}

	public function addDirectory($dir)
	{
		foreach ($this->getRecursiveIterator($dir) as $item) {
			if ($item->isFile()) {
				$this->addFile($item);
			}
		}
		return $this;
	}
	
	public function removeDirectory($dir)
	{
    	if (!is_dir($dir)) {
        	throw new UnexpectedValueException('Unable to remove directory because "' . $dir . '" is not a directory.');
    	}
    	
    	$real = realpath($dir);
    	foreach ($this->files as $index => $file) {
        	if (strpos($file->__toString(), $real) === 0) {
            	$this->files->offsetUnset($index);
        	}
    	}
    	
    	return $this;
	}
	
	public function is($pattern, $mods = null)
	{
    	return $this->filter(function($file) use ($pattern, $mods) {
    	    return preg_match('#' . $pattern . '#' . $mods, $file->__toString()) === 1;
    	});
	}
	
	public function not($pattern, $mods = null)
	{
    	return $this->filter(function($file) use ($pattern, $mods) {
            return preg_match('#' . $pattern . '#' . $mods, $file->__toString()) === 0;
    	});
	}
	
	public function filter(Closure $filter)
	{
        $unset = [];

	    foreach ($this->files as $index => $file) {
	        if ($filter($file) === false) {
	            $unset[] = $index;
	        }
	    }

        foreach ($unset as $index) {
            unset($this->files[$index]);
        }

	    return $this;
	}
	
	public function getTestedFiles()
	{
    	$files = new ArrayIterator;
    	foreach ($this->files as $file) {
    	    if ($file->isTested()) {
    	        $files->offsetSet(null, $file);
    	    }
    	}
    	return $files;
	}
	
	public function getUntestedFiles()
	{
    	$files = new ArrayIterator;
    	foreach ($this->files as $file) {
        	if ($file->isUntested()) {
            	$files->offsetSet(null, $file);
        	}
    	}
    	return $files;
	}
	
	public function getDeadFiles()
	{
    	$files = new ArrayIterator;
    	foreach ($this->files as $file) {
        	if ($file->isDead()) {
            	$files->offsetSet(null, $file);
        	}
    	}
    	return $files;
	}

	public function getUntestedFileCount()
	{
	    return $this->getUntestedFiles()->count();
	}

	public function getTestedFileCount()
	{
	    return $this->getTestedFiles()->count();
	}

	public function getDeadFileCount()
	{
	    return $this->getDeadFiles()->count();
	}
	
	public function getLineCount()
	{
    	return $this->getSumOf('count');
    }
    
    public function getExecutedLineCount()
    {
        return $this->getSumOf('getExecutedLineCount');
    }
    
    public function getUnexecutedLineCount()
    {
        return $this->getSumOf('getUnexecutedLineCount');
    }
    
    public function getDeadLineCount()
    {
        return $this->getSumOf('getDeadLineCount');
    }

	public function getPercentTested($precision = 0)
	{
	    $sum     = $this->getSumOf('getPercentTested');
	    $all     = $this->count() * 100;
	    $percent = $sum / $all * 100;
	    
	    return round(number_format($percent, $precision), $precision);
	}

	private function getRecursiveIterator($dir)
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }
    
    private function getSumOf($method)
    {
        $sum = 0;
        foreach ($this->files as $file) {
            $sum += $file->$method();
        }
        return $sum;
    }
}