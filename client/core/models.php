<?php  

require_once 'dbConfig.php';

function checkIfUserExists($pdo, $username) {
	$response = array();
	$sql = "SELECT * FROM fiverr_users WHERE username = ?";
	$stmt = $pdo->prepare($sql);

	if ($stmt->execute([$username])) {

		$userInfoArray = $stmt->fetch();

		if ($stmt->rowCount() > 0) {
			$response = array(
				"result"=> true,
				"status" => "200",
				"userInfoArray" => $userInfoArray
			);
		}

		else {
			$response = array(
				"result"=> false,
				"status" => "400",
				"message"=> "User doesn't exist from the database"
			);
		}
	}

	return $response;

}

function insertNewUser($pdo, $username, $first_name, $last_name, $password) {
	$response = array();
	$checkIfUserExists = checkIfUserExists($pdo, $username); 

	if (!$checkIfUserExists['result']) {

		$sql = "INSERT INTO fiverr_users (username, first_name, last_name, is_client, password) 
		VALUES (?,?,?,?,?)";

		$stmt = $pdo->prepare($sql);

		if ($stmt->execute([$username, $first_name, $last_name, true, $password])) {
			$response = array(
				"status" => "200",
				"message" => "User successfully inserted!"
			);
		}

		else {
			$response = array(
				"status" => "400",
				"message" => "An error occured with the query!"
			);
		}
	}

	else {
		$response = array(
			"status" => "400",
			"message" => "User already exists!"
		);
	}

	return $response;
}

// Gig entity

function getGigById($pdo, $gig_id) {
	$sql = "SELECT * FROM gigs WHERE gig_id = ?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$gig_id]);
	return $stmt->fetch();
}


function getAllGigs($pdo) {
	$sql = "SELECT
				fiverr_users.username AS username, 
				gigs.gig_id AS gig_id, 
				gigs.gig_title AS title, 
				gigs.gig_description AS description, 
				gigs.date_added AS date_added 
			FROM fiverr_users
			JOIN gigs ON fiverr_users.user_id = gigs.user_id
			ORDER BY date_added DESC
			";
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	return $stmt->fetchAll();
}

function getAllGigsByUserId($pdo, $user_id) {
	$sql = "SELECT
				fiverr_users.username AS username, 
				gigs.gig_id AS gig_id, 
				gigs.gig_title AS title, 
				gigs.gig_description AS description, 
				gigs.date_added AS date_added 
			FROM fiverr_users
			JOIN gigs ON fiverr_users.user_id = gigs.user_id
			WHERE fiverr_users.user_id = ?
			ORDER BY date_added DESC
			";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$user_id]);
	return $stmt->fetchAll();
}

function insertNewGig($pdo, $gig_title, $gig_description, $user_id) {
	$sql = "INSERT INTO gigs (gig_title, gig_description, user_id) 
			VALUES (?,?,?)";
	$stmt = $pdo->prepare($sql);
	return $stmt->execute([$gig_title, $gig_description, $user_id]);
}

function updateGig($pdo, $gig_title, $gig_description, $gig_id) {
	$sql = "UPDATE gigs SET gig_title = ?, gig_description = ? WHERE gig_id = ?";
	$stmt = $pdo->prepare($sql);
	return $stmt->execute([$gig_title, $gig_description, $gig_id]);
}

function deleteGig($pdo, $gig_id) {
	$sql = "DELETE FROM gigs WHERE gig_id = ?";
	$stmt = $pdo->prepare($sql);
	if (deleteAllProposalsByGig($pdo, $gig_id) && deleteAllInterviewsByGig($pdo, $gig_id)) {
		return $stmt->execute([$gig_id]);
	}
}

function deleteAllProposalsByGig($pdo, $gig_id) {
	$sql = "DELETE FROM gig_proposals WHERE gig_id = ?";
	$stmt = $pdo->prepare($sql);
	return $stmt->execute([$gig_id]);
}

function deleteAllInterviewsByGig($pdo, $gig_id) {
	$sql = "DELETE FROM gig_interviews WHERE gig_id = ?";
	$stmt = $pdo->prepare($sql);
	return $stmt->execute([$gig_id]);
}

// Gig proposals
function getProposalsByGigId($pdo, $gig_id) {
	$sql = "SELECT 
				fiverr_users.user_id AS user_id,
				fiverr_users.first_name AS first_name,
				fiverr_users.last_name AS last_name,
				gig_proposals.gig_proposal_description AS description,
				gig_proposals.date_added AS date_added
			FROM fiverr_users 
			JOIN gig_proposals ON fiverr_users.user_id = gig_proposals.user_id
			WHERE gig_id = ?
			";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$gig_id]);
	return $stmt->fetchAll();	
}



// Gig Interview entity
function checkIfUserAlreadyScheduled($pdo, $freelancer_id, $gig_id) {
	$sql = "SELECT * FROM gig_interviews WHERE freelancer_id = ? AND gig_id = ?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$freelancer_id, $gig_id]);
	if ($stmt->rowCount() > 0) {
		return true;
	}
}

function getAllInterviewsByGig($pdo, $gig_id) {
	$sql = "SELECT 
				fiverr_users.first_name AS first_name,
				fiverr_users.last_name AS last_name,
				gig_interviews.time_start AS time_start,
				gig_interviews.time_end AS time_end,
				gig_interviews.status AS status
			FROM fiverr_users JOIN gig_interviews 
			ON fiverr_users.user_id = gig_interviews.freelancer_id
			WHERE gig_interviews.gig_id = ?";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([$gig_id]);
	return $stmt->fetchAll();
}

/*
function insertNewGigInterview($pdo, $gig_id, $freelancer_id, $time_start, $time_end) {

	if (!checkIfUserAlreadyScheduled($pdo, $freelancer_id, $gig_id)) {
		$sql = "INSERT INTO gig_interviews (gig_id, freelancer_id, time_start, time_end) 
			VALUES (?,?,?,?)";
		$stmt = $pdo->prepare($sql);
		return $stmt->execute([$gig_id, $freelancer_id, $time_start, $time_end]);
	}

}
*/

// new
function insertNewGigInterview($pdo, $gig_id, $freelancer_id, $time_start, $time_end) {
    // Convert to datetime objects for validation
    $now = new DateTime();
    $start = new DateTime($time_start);
    $end = new DateTime($time_end);

    if ($start < $now || $end < $now) {
        return 'past_date';
    }

    if ($start >= $end) {
        return 'invalid_range';
    }

    // Check for conflicting interviews for the same client (gig_id)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM gig_interviews
        WHERE gig_id = :gig_id
        AND (
            (time_start < :time_end AND time_end > :time_start)
        )
    ");
    $stmt->execute([
        ':gig_id' => $gig_id,
        ':time_start' => $time_start,
        ':time_end' => $time_end
    ]);

    if ($stmt->fetchColumn() > 0) {
        return 'conflict';
    }

    // Check if this freelancer already has an interview for this gig
    if (!checkIfUserAlreadyScheduled($pdo, $freelancer_id, $gig_id)) {
        $sql = "INSERT INTO gig_interviews (gig_id, freelancer_id, time_start, time_end) 
                VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([$gig_id, $freelancer_id, $time_start, $time_end]);
        return $success ? 'success' : 'error';
    } else {
        return 'duplicate';
    }
}



function updateGigInterview($pdo, $gig_title, $gig_description, $gig_id) {
	$sql = "UPDATE gig_interviews SET time_start = ?, time_end = ? WHERE gig_interview_id = ?";
	$stmt = $pdo->prepare($sql);
	return $stmt->execute([$gig_title, $gig_description, $gig_id]);
}

/*
function deleteGigInterview($pdo, $gig_interview_id) {
	$sql = "DELETE FROM gig_interviews WHERE gig_interview_id = ?";
	$stmt = $pdo->prepare($sql);
	return $stmt->execute([$gig_id]);	
}
*/


function getNumOfProposalsByClient($pdo, $client_id) {
  $sql = "SELECT COUNT(*) AS proposalCount 
          FROM gig_proposals 
          WHERE gig_id IN (
            SELECT gig_id FROM gigs WHERE user_id = ?
          )";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$client_id]);
  return $stmt->fetch();
}