<?php
include_once 'config.php';

$youtrack = new YouTrack\Connection(
    YOUTRACK_URL,
    YOUTRACK_USERNAME,
    YOUTRACK_PASSWORD
);

$basePath = '../import/';

$files = scandir($basePath, SCANDIR_SORT_DESCENDING);

$storyId = $files[0];

$issue = $youtrack->getIssue($storyId);
$fixVersion = $issue->getFixVersion();

$tasks = file($basePath . $storyId);

$toCreate = [];

foreach ($tasks as $task) {
	preg_match("/^- (.+)$/", $task, $data);

	if (count($data) != 2) {
		echo "Couldn't parse: " . $task;
		echo "Nothing was created\n";
		exit;
	}

	$params = array(
	    'priority' => 'Normal',
	    'type' => 'Task',
	    // 'estimation' => $data[1],
	    'title' => $data[1],
	);

	$toCreate[] = $params;
}

var_dump($toCreate);exit;

foreach ($toCreate as $index => $task) {
	$issue = $youtrack->createIssue(YOUTRACK_PROJECT, $task['title'], $params);
	$youtrack->createChildLink($storyId, $issue->getId());
	$youtrack->executeCommand($issue->getId(), 'Estimation ' . $task['estimation'] . 'h');

	if (!is_null($fixVersion)) {
		$youtrack->executeCommand($issue->getId(), 'Fix versions ' . $fixVersion);
	}
	
	// if ($index === 3) {
	// 	exit;
	// }
}