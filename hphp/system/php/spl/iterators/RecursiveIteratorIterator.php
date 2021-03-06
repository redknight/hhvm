<?php

// This doc comment block generated by idl/sysdoc.php
/**
 * ( excerpt from
 * http://php.net/manual/en/class.recursiveiteratoriterator.php )
 *
 * Can be used to iterate through recursive iterators.
 *
 */
class RecursiveIteratorIterator implements OuterIterator {

  const LEAVES_ONLY = 0;
  const SELF_FIRST = 1;
  const CHILD_FIRST = 2;
  const CATCH_GET_CHILD = 16;

  private $iterators = array();
  private $originalIterator;
  private $mode;
  private $flags;
  private $maxDepth = -1;

  // This doc comment block generated by idl/sysdoc.php
  /**
   * ( excerpt from
   * http://php.net/manual/en/recursiveiteratoriterator.construct.php )
   *
   * Creates a RecursiveIteratorIterator from a RecursiveIterator.
   *
   * @iterator   mixed   The iterator being constructed from. Either a
   *                     RecursiveIterator or IteratorAggregate.
   * @mode       mixed   Optional mode. Possible values are
   *                     RecursiveIteratorIterator::LEAVES_ONLY - The
   *                     default. Lists only leaves in iteration.
   *                     RecursiveIteratorIterator::SELF_FIRST - Lists leaves
   *                     and parents in iteration with parents coming first.
   *                     RecursiveIteratorIterator::CHILD_FIRST - Lists
   *                     leaves and parents in iteration with leaves coming
   *                     first.
   * @flags      mixed   Optional flag. Possible values are
   *                     RecursiveIteratorIterator::CATCH_GET_CHILD which
   *                     will then ignore exceptions thrown in calls to
   *                     RecursiveIteratorIterator::getChildren().
   *
   * @return     mixed   No value is returned.
   */
  public function __construct(\Traversable $iterator,
                              $mode = RecursiveIteratorIterator::LEAVES_ONLY,
                              $flags = 0) {
    if ($iterator && ($iterator instanceof IteratorAggregate)) {
      $iterator = $iterator->getIterator();
    }
    if (!$iterator || !($iterator instanceof RecursiveIterator)) {
      throw new InvalidArgumentException(
        "An instance of RecursiveIterator or IteratorAggregate creating " .
        "it is required"
      );
    }
    $this->iterators[] = array($iterator, 0);
    $this->originalIterator = $iterator;
    $this->mode = (int) $mode;
    $this->flags = $flags;
  }

  // This doc comment block generated by idl/sysdoc.php
  /**
   * ( excerpt from
   * http://php.net/manual/en/recursiveiteratoriterator.getinneriterator.php
   * )
   *
   * Gets the current active sub iterator. Warning: This function is
   * currently not documented; only its argument list is available.
   *
   * @return     mixed   The current active sub iterator.
   */
  public function getInnerIterator() {
    $it = $this->iterators[count($this->iterators)-1][0];
    if (!$it instanceof RecursiveIterator) {
      throw new Exception(
        "inner iterator must implement RecursiveIterator"
      );
    }
    return $it;
  }

  // This doc comment block generated by idl/sysdoc.php
  /**
   * ( excerpt from
   * http://php.net/manual/en/recursiveiteratoriterator.current.php )
   *
   *
   * @return     mixed   The current elements value.
   */
  public function current() {
    return $this->getInnerIterator()->current();
  }

  // This doc comment block generated by idl/sysdoc.php
  /**
   * ( excerpt from
   * http://php.net/manual/en/recursiveiteratoriterator.key.php )
   *
   *
   * @return     mixed   The current key.
   */
  public function key() {
    return $this->getInnerIterator()->key();
  }

  // This doc comment block generated by idl/sysdoc.php
  /**
   * ( excerpt from
   * http://php.net/manual/en/recursiveiteratoriterator.next.php )
   *
   *
   * @return     mixed   No value is returned.
   */
  public function next() {
    if ($this->isEmpty()) {
      return;
    }

    $it = $this->getInnerIterator();
    $maxDepthReached =
      ($this->maxDepth > -1 && $this->getDepth() >= $this->maxDepth);

    if ($this->mode == self::SELF_FIRST) {
      if ($this->callHasChildren() &&
          !$this->getInnerIteratorFlag() &&
          !$maxDepthReached) {
        $this->setInnerIteratorFlag(1);
        $newit = $this->callGetChildren();
        $newit->rewind();
        $this->iterators[] = array($newit, 0);
        $this->beginChildren();
      } else {
        $it->next();
        $this->setInnerIteratorFlag(0);
      }

      if ($this->valid()) {
        $this->nextElement();
        return;
      }
      if (count($this->iterators) > 1) {
        $this->endChildren();
      }
      array_pop($this->iterators);
      return $this->next();
    } else if ($this->mode == self::CHILD_FIRST ||
               $this->mode == self::LEAVES_ONLY) {
      if (!$it->valid()) {
        if (count($this->iterators) > 1) {
          $this->endChildren();
        }
        array_pop($this->iterators);
        return $this->next();
      } else if ($this->callHasChildren() && !$maxDepthReached) {
        if (!$this->getInnerIteratorFlag()) {
          $this->setInnerIteratorFlag(1);
          $newit = $this->callGetChildren();
          $newit->rewind();
          $this->iterators[] = array($newit, 0);
          $this->beginChildren();
          if ($this->valid()) {
            $this->nextElement();
            return;
          }
          return $this->next();
        } else {
          // CHILD_FIRST: 0 - drill down; 1 - visit 2 - next
          // LEAVES_ONLY: 0 - drill down; 1 - next
          if ($this->mode == self::CHILD_FIRST &&
              $this->getInnerIteratorFlag() == 1) {
              $this->setInnerIteratorFlag(2);
            $this->nextElement();
            return;
          }
        }
      }

      $this->setInnerIteratorFlag(0);
      $it->next();
      if ($this->valid()) {
        $this->nextElement();
        return;
      }
      return $this->next();
    } else {
      $this->setInnerIteratorFlag(0);
      $it->next();
      $this->nextElement();
    }
  }

  // This doc comment block generated by idl/sysdoc.php
  /**
   * ( excerpt from
   * http://php.net/manual/en/recursiveiteratoriterator.rewind.php )
   *
   *
   * @return     mixed   No value is returned.
   */
  public function rewind() {
    while (count($this->iterators) > 1) {
      $this->endChildren();
      array_pop($this->iterators);
    }

    $it = $this->originalIterator;
    $this->iterators = array(array($it, 0));
    $it->rewind();
    $this->beginIteration();

    // Make sure the first entry is valid
    if (!$this->valid()) {
      $this->next();
    }
    else {
      $this->nextElement();
    }
  }

  // This doc comment block generated by idl/sysdoc.php
  /**
   * ( excerpt from
   * http://php.net/manual/en/recursiveiteratoriterator.valid.php )
   *
   *
   * @return     mixed   TRUE if the current position is valid, otherwise
   *                     FALSE
   */
  public function valid() {
    if ($this->isEmpty()) {
      $this->endIteration();
      return false;
    }

    $it = $this->getInnerIterator();
    if ($it->valid() &&
        $this->callHasChildren() &&
        ($this->mode == self::LEAVES_ONLY ||
         ($this->mode == self::CHILD_FIRST &&
          $this->getInnerIteratorFlag() == 0))) {
      return false;
    }
    return $it->valid();
  }

  /**
   * Called after calling getChildren(), and its associated rewind().
   */
  public function beginChildren()
  {
  }

  /**
   * Called when iteration begins (after the first rewind() call).
   */
  public function beginIteration()
  {
  }

  /**
   * Get children of the current element.
   *
   * @return     RecursiveIterator
   */
  public function callGetChildren()
  {
    return $this->getInnerIterator()->getChildren();
  }

  /**
   * Called for each element to test whether it has children.
   *
   * @return     bool
   */
  public function callHasChildren()
  {
    return $this->getInnerIterator()->hasChildren();
  }

  /**
   * Called when end recursing one level.
   */
  public function endChildren()
  {
  }

  /**
   * Called when the iteration ends (when valid() first returns FALSE).
   */
  public function endIteration()
  {
  }

  /**
   * Get the current depth of the recursive iteration.
   *
   * @return     int     The current depth of the recursive iteration.
   */
  public function getDepth()
  {
    return count($this->iterators)-1;
  }

  /**
   * Gets the maximum allowable depth.
   *
   * @return     int     The maximum accepted depth, or FALSE if any depth is
   *                     allowed.
   */
  public function getMaxDepth()
  {
    return ($this->maxDepth == -1) ? false : $this->maxDepth;
  }

  /**
   * Gets the current active sub iterator.
   *
   * @param      int     $level
   *
   * @return     RecursiveIterator   The current active sub iterator.
   */
  public function getSubIterator($level = null)
  {
    $currentLevel = count($this->iterators)-1;
    if (is_null($level)) {
      $level = $currentLevel;
    }
    if ($level < 0 || $level > $currentLevel) {
      return null;
    }
    return $this->iterators[$level][0];
  }

  /**
   * Called when the next element is available.
   */
  public function nextElement()
  {
  }

  /**
   * Set the maximum allowed depth.
   *
   * @param      int     $max_depth   The maximum allowed depth. -1 is used for
   *                                  any depth.
   *
   * @throws     Exception            Emits an Exception if max_depth is less
   *                                  than -1.
   */
  public function setMaxDepth($max_depth = -1)
  {
    if ($max_depth < -1) {
      throw new OutOfRangeException("Parameter max_depth must be >= -1");
    }

    $this->maxDepth = $max_depth;
  }

  private function isEmpty() {
    return count($this->iterators) == 0;
  }

  private function getInnerIteratorFlag() {
    return $this->iterators[count($this->iterators)-1][1];
  }

  private function setInnerIteratorFlag($flag) {
    $this->iterators[count($this->iterators)-1][1] = $flag;
  }

  /**
   * Undocumented behavior but Zend does it and frameworks rely on it, so..
   */
  public function __call($func, $params) {
    return call_user_func_array(array($this->current(), $func), $params);
  }
}
