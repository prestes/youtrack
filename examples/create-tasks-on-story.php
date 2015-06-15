<?php
include_once 'config.php';

$youtrack = new YouTrack\Connection(
    YOUTRACK_URL,
    YOUTRACK_USERNAME,
    YOUTRACK_PASSWORD
);

$basePath = '../import/';

$files = scandir($basePath, SCANDIR_SORT_DESCENDING);

$file = $files[0];

var_dump($files);exit;

$issue = $youtrack->getIssue($file);
$fixVersion = $issue->getFixVersion();

$tasks = file($basePath . $file);

$toCreate = [];

foreach ($tasks as $task) {
	preg_match("/^\[(\d+)\] (.+)$/", $task, $data);

	if (count($data) != 3) {
		echo "Couldn't parse: " . $task;
		echo "Nothing was created\n";
		exit;
	}

	$params = array(
	    'priority' => 'Normal',
	    'type' => 'Task',
	    'estimation' => $data[1],
	    'title' => $data[2],
	);

	$toCreate[] = $params;
}

foreach ($toCreate as $index => $task) {
	$issue = $youtrack->createIssue('W', $task['title'], $params);
	$youtrack->createChildLink($file, $issue->getId());
	$youtrack->executeCommand($issue->getId(), 'Estimation ' . $task['estimation'] . 'h');

	if (!is_null($fixVersion)) {
		$youtrack->executeCommand($issue->getId(), 'Fix versions ' . $fixVersion);
	}
	
	// if ($index === 3) {
	// 	exit;
	// }
}