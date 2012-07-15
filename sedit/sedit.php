<?php

session_start();
include_once('config.php');

/**
 * SeditFile Class
 * Parent base class defines accessors and common methods and their implementations
 */
class Foundation {
  // Only get or set properties that are declared within the class
  public function __get($prop) {
    if (array_key_exists($prop, get_object_vars($this)))
      return $this->$prop;
  }
  public function __set($prop, $val) {
    if (array_key_exists($prop, get_object_vars($this)))
      $this->$prop = $val;
  }
}

/**
 * SeditFile Class
 * Useful file based operations
 */
class SeditFile extends Foundation {

  protected $_filename;
  protected $_path = DATA_STORE;
  protected $_content;
  
  public function ifExists() {
    return file_exists($this->_path. '/' .$this->_filename);
  }
  public function isWriteable() {
    return is_writable($this->_path . '/' . $this->_filename);
  }
  public function write() {
    $fh = fopen($this->_path . '/' . $this->_filename, 'w');
    if (!$fh) return false;
    flock($fh, LOCK_EX);
    fwrite($fh, $this->_content);
    flock($fh, LOCK_UN);
    fclose($fh);
    return true;
  }
  public function read() {
    if($fh = fopen($this->_path . '/' . $this->_filename, 'r')){
      return fread($fh, filesize($this->_path . '/' . $this->_filename));
    }
    else return false;
  }
  public function delete() {
    unlink($this->_path.'/'.$this->_filename);
  }
  
}

/**
 * SeditFile Class
 * Useful file based operations
 */
class SeditController extends Foundation {
  protected $_action; // @string either 'read' or 'write'
  protected $_elementid; // @string optional, elementid for multiple editable regions on one page
  protected $_pathname; // @string value of window.location.pathname
  protected $_login; // @boolean if user has authenticated with sedit (write access)
  protected $_content; // @string content to save
  
  /**
   * Splits the location.pathname value into a filename eith appropraite namespace for the data store
   * @param type $page 
   * @return string pathname.filename.txt
   */
  public function compileFilename(){
    
    // local vars
    $pathname = $this->_pathname;
    $elementid = $this->_elementid ? $this->_elementid : false;
    
    // if leading forward slash, strip it
    $pathname = ((substr($pathname, 0, 1))==='/') ? substr($pathname, 1) : $pathname;

    // if trailing forward slash or peroid for file extension, flag directoryIndex and strip it
    if ( (substr($pathname, -1)=='/') || (strpos($pathname, '.')===false) ) {
      $directoryIndex = true;
      $pathname = substr($pathname, 0, -1);
    }    

    // filename format is to seperate directories with forward slash
    $filename = str_replace('/', '.', $pathname);

    if (isset($directoryIndex)) $filename .= '.' . DIRECTORY_INDEX;
    
    if ($elementid){
      $filename .= '#' . $elementid;
    }
    
    return $filename .= '.txt';
  }

  public function performRead($F){
    if ($F->ifExists()){
      $content = $F->read();
      $status = 'success';
    }
    else {
      $content = 'Please insert some data here';
      $status = 'fail';
    }
    return array('status' => $status, 'content'=> $content, 'elementid'=> $this->_elementid);
  }

  public function performWrite($F){
    if ($F->write($F->_content)) {
      $status = 'success';
      $content = $F->_content;
    } else {
      $content = 'Error: Could not write to file';
      $status = 'fail';
    }
    return array('status' => $status, 'content'=> $content, 'elementid'=> $this->_elementid);
  }

}

// dummy
//$_POST['pathname'] = '/sedit-demo/';
//$_POST['action'] = 'write';
//$_POST['content'] = 'Ben Cooling rules in the world';
//$_POST['elementid'] = 'foo';
//$_POST['elementid'][] = 'bah';

$S = new SeditController();
$S->_pathname = $_POST['pathname'];
$S->_action = $_POST['action'];

$F = new SeditFile();
$F->_path = DATA_STORE;

$output = array();

if (($S->_action)=='read'){
  $elementids = (isset($_POST['elementid'])) ? $_POST['elementid'] : false;
  if (is_array($elementids)) foreach($elementids as $elementid){
    $S->_elementid = $elementid;
    $F->_filename = ($S->compileFilename());
    $regionOutput = $S->performRead($F);
    array_push($output, $regionOutput);
  }
  else {
    $F->_filename = ($S->compileFilename());
    $regionOutput = $S->performRead($F);
    array_push($output, $regionOutput);
  }
}
// TODO: Add support for multiple region write
else if (($S->_action)=='write'){
  
  $F->_content = $_POST['content'];
  $S->_login= (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) ? true : false;
  $S->_elementid = $_POST['elementid'];
  
  $F->_filename = ($S->compileFilename());
  $regionOutput = $S->performWrite($F);
  array_push($output, $regionOutput);
  
}
else {
  echo json_encode(array('status'=>'error'));
  exit;
}

$loggedin = (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) ? $_SESSION['authenticated'] : false;
// array(array('status' => $status, 'content'=> $content, 'elementid'=> $elementid), 'loggedin'=>$loggedin);
echo json_encode(array('regions'=>$output) + array('loggedin'=>$loggedin));