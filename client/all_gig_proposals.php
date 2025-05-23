<?php 
require_once 'core/dbConfig.php'; 
require_once 'core/models.php'; 

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
}

if ($_SESSION['is_client'] == 0) {
  header("Location: ../freelancer/index.php");
}

$client_id = $_SESSION['user_id'];
$gigs = getAllGigsByUserId($pdo, $client_id);

$allProposals = [];

foreach ($gigs as $gig) {
  $proposals = getProposalsByGigId($pdo, $gig['gig_id']);
  foreach ($proposals as $proposal) {
    $allProposals[] = [
      'gig_id' => $gig['gig_id'],
      'gig_title' => $gig['title'],
      'gig_description' => $gig['description'],
      'freelancer_name' => $proposal['last_name'] . ", " . $proposal['first_name'],
      'description' => $proposal['description'],
      'date_added' => $proposal['date_added']
    ];
  }
}

// Sort proposals by latest date
usort($allProposals, function($a, $b) {
  return strtotime($b['date_added']) - strtotime($a['date_added']);
});
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>All Proposals for Your Gigs</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <style>
    body {
      font-family: Arial;
    }
    .proposal-card {
      transition: 0.3s;
    }
    .proposal-card:hover {
      transform: scale(1.02);
    }
  </style>
</head>
<body>
  <?php include 'includes/navbar.php'; ?>
  <div class="container mt-5">
    <h1 class="mb-4 text-center">All Proposals</h1>

    <?php if (empty($allProposals)) { ?>
      <div class="alert alert-info">No proposals have been made on your gigs yet.</div>
    <?php } ?>

    <div class="row">
      <?php foreach ($allProposals as $proposal) { ?>
      <div class="col-md-4 mt-3">
        <div class="card shadow proposal-card p-3">
          <h5 class="text-primary"><?php echo htmlspecialchars($proposal['gig_title']); ?></h5>
          <small class="text-muted"><?php echo htmlspecialchars($proposal['gig_description']); ?></small>
          <hr>
          <p><?php echo nl2br(htmlspecialchars($proposal['description'])); ?></p>
          <p><strong>From:</strong> <?php echo $proposal['freelancer_name']; ?></p>
          <small class="text-muted">Submitted on: <?php echo $proposal['date_added']; ?></small><br>
          <a href="get_gig_proposals.php?gig_id=<?php echo $proposal['gig_id']; ?>" class="btn btn-sm btn-primary mt-2">View All for This Gig</a>
        </div>
      </div>
      <?php } ?>
    </div>
  </div>
  <?php include 'includes/footer.php'; ?>
</body>
</html>