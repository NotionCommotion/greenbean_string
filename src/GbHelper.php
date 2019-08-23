<?php
/**
Or should this class be located in the package's controller directory?
If so, change path to __DIR__.'/../../single_pages' assuming namespace Concrete\Package\GreenbeanString\Controller\SinglePage.
*/
namespace Greenbean\Concrete5\GreenbeanString;
class GbHelper
{
    private $filetypes=['js'=>'JavaScript','css'=>'CSS'];

    public function sortSources(array $sourcesIn):array
    {
        $sources=['bacnetGateways'=>[],'modbusGateways'=>[],'webServices'=>[]];
        foreach($sourcesIn as $row) {
            if($row['type']=='gateway' && $row['protocol']=='bacnet') {
                $sources['bacnetGateways'][]=$row;
            }
            elseif($row['type']=='gateway' && $row['protocol']=='modbus') {
                $sources['modbusGateways'][]=$row;
            }
            elseif($row['type']=='webservice') {
                $sources['webServices'][]=$row;
            }
            else {
                syslog(LOG_ERR, "Invalid source type: ".json_encode($row));
            }
        }
        return $sources;
    }

    public function getDefaultValues()
    {
        return [
            "id" => 0,
            "name" => null,
            "timezone" => null,
            "roundingPrecision" => 6,
            "setNullPointTo" => 0,
            "allowNullInAssembledPoint" => 0,
            "virtualLanId" => 1,
            "tsConfigUpdated" => null,
            "database" => [
                "cacheTrends" => 60,
                "cachePoints" => 2,
                "cachePastPoints" => 0
            ],
            "realPnts" => [
                "trend" => 1
            ],
            "bacnet" => [
                "unit" => 1,
                "covLifetime" => 300,
                "pollrate" => 60,
                "port" => 47808,
                "timeout" => 42,
                "discoveryTimeout" => 5
            ],
            "webservice" => [
                "pollrate" => 60,
                "port" => 80,
                "timeout" => 100
            ],
            "gateway" => [
                "reconnectTimeout" => 5,
                "responseTimeout" => 10,
                "historyPackSize" => 10
            ]
        ];
    }

    public function getDefaultReportValues()
    {
        return [
            'id'=>0,
            'name'=>'',
            'aggrTimeValue'=>1,
            'aggrTimeUnit'=>'w',
            'histTimeValue'=>0,
            'histTimeUnit'=>'w',
            'periodTimeValue'=>1,
            'periodTimeUnit'=>'h',
            'points'=>[]
        ];
    }

    public function debugDump($v, string $label, $options=[])
    {
        //$options ['log'=>TRUE/false, 'format'=>JSON/var_dump]
        $options=array_merge(['log'=>true, 'format'=>'var_dump'],$options);
        if($options['format']=='json') {
            $results=json_encode($v);
        }
        else {
            ob_start();
            var_dump($v);
            $results=ob_get_clean();
        }
        $results=$label.': '.$results;
        if($options['log']) syslog(LOG_INFO, $results);
        return $results;
    }

    /*
    public function updatePage($page,$html)
    {
        $stmt=$this->pdo->prepare('INSERT OR REPLACE INTO pages(id, html) VALUES(?, ?)');
        $stmt->execute([$page, $html]);
    }

    public function getResources(int $pageId)
    {
        $stmt=$this->pdo->prepare('SELECT r.id, r.filename, r.size, r.date_created, r.type, phr.resources_id
            FROM resources r LEFT OUTER JOIN pages_has_resources phr ON phr.resources_id=r.id AND phr.pages_id=?
        ORDER BY r.type, r.filename');
        $stmt->execute([$pageId]);
        $files=[];
        foreach($stmt as $f) {
            $files[]=['id'=>$f['id'],'filename'=>$f['filename'],'file'=>'/lib/user/'.$f['filename'],'size'=>$f['size'],'date'=>$f['date_created'],'type'=>$this->filetypes[$f['type']],'linked'=>$f['resources_id']?true:false];
        }
        return $files;
    }

    public function addResource(int $pageId, $uploadedFiles)
    {
        if(empty($uploadedFiles['resource']))  throw new CustomException('missing file',422);
        $uploadedFile=$uploadedFiles['resource'];   //Will be an instance of \Slim\Http\UploadedFile
        $error=$uploadedFile->getError();
        if ($error === UPLOAD_ERR_OK) {
            $filename=$uploadedFile->getClientFilename();
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if(!isset($this->filetypes[$ext])) throw new CustomException('invalid file type, only '.implode(' ,',array_values(array_flip($this->filetypes))).' are allowed.',422);
            $mimeClient=$uploadedFile->getClientMediaType();
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            //$mime=finfo_file($finfo, $uploadedFile->name);    //Protected
            $mimeFinfo=finfo_file($finfo, $_FILES['resource']['tmp_name']);
            finfo_close($finfo);
            //syslog(LOG_INFO,"filename: $filename ext: $ext mimeFinfo: $mimeFinfo mimeClient: $mimeClient");
            $mimeConv=['application/javascript'=>'text/plain','text/javascript'=>'text/plain','text/css'=>'text/plain'];
            if(isset($mimeConv[$mimeClient])) {
                $mimeClient=$mimeConv[$mimeClient];
            }
            if($mimeFinfo!=$mimeClient) {
                throw new CustomException("Corrupt file. $mimeFinfo not equal to $mimeClient",422);
            }
            //Validate size, etc if desired
            //If file exists, overwrite, else add
            $stmt=$this->pdo->prepare('SELECT id FROM resources WHERE filename=?');
            $stmt->execute([$filename]);
            if($resourceId=$stmt->fetchColumn()) {
                $this->pdo->prepare('UPDATE resources SET size=?, date_created=DATETIME("now") WHERE id=?')
                ->execute([$uploadedFile->getSize(), $resourceId]);
            }
            else {
                $this->pdo->prepare('INSERT INTO resources(filename, type, size) VALUES(?,?,?)')
                ->execute([$filename,$ext,$uploadedFile->getSize()]);
                $resourceId=$this->pdo->lastInsertId();
            }
            $this->pdo->prepare('INSERT INTO pages_has_resources(pages_id, resources_id) VALUES(?,?)')->execute([$pageId, $resourceId]);
            $relPath='/lib/user/'.$filename;
            $absPath=realpath(__DIR__."/../../html").$relPath;
            $uploadedFile->moveTo($absPath);
            return $resourceId;
        }
        else {
            $error=[
                UPLOAD_ERR_INI_SIZE=>'File exceeds maximum size.',
                UPLOAD_ERR_FORM_SIZE=>'File exceeds maximum size.',
                UPLOAD_ERR_PARTIAL=>'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE=>'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR=>'File was not uploaded.',
                UPLOAD_ERR_CANT_WRITE=>'File was not uploaded.',
                UPLOAD_ERR_EXTENSION=>'File was not uploaded.'
            ][$error];
            throw new CustomException($error,422);
        }
    }

    public function updateResourceLink(int $page,int $resourceId, $linked)
    {
        $sql=$linked
        ?'INSERT INTO pages_has_resources (pages_id, resources_id) VALUES(?,?)'
        :'DELETE FROM pages_has_resources WHERE pages_id=? AND resources_id=?';
        $this->pdo->prepare($sql)->execute([$page, $resourceId]);
        return null;
    }

    public function deleteResource(int $fileId)
    {
        if($filename=$this->getFileName($fileId)) {
            $this->pdo->prepare('DELETE FROM resources WHERE id=?')->execute([$fileId]);
            $file=realpath(__DIR__."/../../html").'/lib/user/'.$filename;
            unlink($file);
            return null;
        }
        else {
            throw new CustomException("File with ID $id does not exist",403);
        }
    }

    private function getFileName(int $fileId):string
    {
        $stmt=$this->pdo->prepare('SELECT filename FROM resources WHERE id=?');
        $stmt->execute([$fileId]);
        return $stmt->fetchColumn();
    }

    public function getHtml(int $page):string
    {
        $stmt=$this->pdo->prepare('SELECT html FROM pages WHERE id=?');
        $stmt->execute([$page]);
        return $stmt->fetchColumn();
    }

    public function getResourceFiles(int $page):array
    {
        $resources=['css'=>[],'js'=>[]];
        $stmt=$this->pdo->prepare('SELECT r.filename, r.type FROM resources r INNER JOIN pages_has_resources phr ON phr.resources_id=r.id AND phr.pages_id=?');
        $stmt->execute([$page]);
        foreach($stmt as $resource){
            $resources[$resource['type']][]='/lib/user/'.$resource['filename'];
        }
        return $resources;
    }
    */
}
