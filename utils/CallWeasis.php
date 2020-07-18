{PHP}
$HOST = "192.168.1.122";
$PORT = "8090";

$gdtpath = "C:\\GDT\\export.gdt";
$gdt = fopen($gdtpath, "r");
$contents = fread($gdt, filesize($gdtpath));
fclose($gdt);

$lines = explode("\r\n", $contents);
$patid = "";
$gdtdate = "";
$gdttime = "";

foreach($lines as $line) {
    sscanf($line, "%3d%4d%s", $len, $type, $content);
    
    if($type == "3000") $patid = $content;
    if($type == "8432") $gdtdate = $content;
    if($type == "8439") $gdttime = $content;
}

sscanf($gdtdate, "%02d%02d%04d", $day, $month, $year);
sscanf($gdttime, "%02d%02d%02d", $hour, $minute, $second);
$studyDate = sprintf("%04d%02d%02d", $year, $month, $day);
$studyTime = sprintf("%02d%02d%02d-%02d%02d%02d", $hour, $minute, $second, $hour, $minute, $second+1);

$data = array(
    "Level" => "Study",
    "Query" => array(
        "StudyDate" => $studyDate,
        "StudyTime" => $studyTime,
        "PatientID" => $patid
    ),
    "Expand" => true
);

$jsdata = json_encode($data);
$options = array(
    'http' => array(
        'method' => 'POST',
        'content' => $jsdata
    )
);
$context = stream_context_create($options);
$result = file_get_contents("http://$HOST:$PORT/tools/find", false, $context);
$js = json_decode($result);
$studyUID = $js[0]->MainDicomTags->StudyInstanceUID;
$myStudyUID = $js[0]->MainDicomTags->StudyInstanceUID;

$weasisCommand = "runweasis.bat \$dicom:close -a \$dicom:rs --url \"http://$HOST:$PORT/dicom-web\" " .
    "-r \"studyUID=$myStudyUID\"";

shell_exec($weasisCommand);
{/PHP}
