<?php require_once __DIR__.'/header.php'; ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 main">
					<h1 class="page-header">Список капперов</h1>

					<?php
						$images_dir = '../images/';

						require_once __DIR__ . '/../vendor/autoload.php';
						require_once __DIR__.'/../CapperDB.php';
						require_once __DIR__.'/../UserDB.php';

						use Longman\TelegramBot\DB;
						
						CapperDB::initializeCapper();
						UserDB::initializeUser();

						if(!file_exists(__DIR__.'/../config.php')) {
							die("Please rename example_config.php to config.php and try again. \n");
						} else {
							require_once __DIR__.'/../config.php';
						}

						try {
							// Create Telegram API object
							$telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

							// Enable MySQL
							$telegram->enableMySql($mysql_credentials);

							$cappers = CapperDB::selectCapper();

							print '<div class="row placeholders">';

							foreach ($cappers as $capper) {
								print '
									<div class="col-xs-6 col-sm-3 placeholder">
										<a href="capper-add.php?id='.$capper['id'].'"><img src="'.$images_dir.current(glob($images_dir.$capper['id'].'.*')).'" class="img-responsive" alt="Generic placeholder thumbnail"></a>
										<h4>'.$capper['name'].'</h4>
										<span class="text-muted">'.$capper['description'].'</span>
									</div>
								';
							}

							print '</div>';

							print '<h2 class="sub-header">Список пользователей</h2>
								<div class="table-responsive">
									<table class="table table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>Имя</th>
												<th>Ник</th>
												<th>Первое посещение</th>
												<th>Последние посещение</th>
												<th>Подписки</th>
											</tr>
										</thead>
										<tbody>
							';


							$users = UserDB::selectUser();

							foreach ($users as $user) {
								print '
											<tr>
												<td>'.$user['id'].'</td>
												<td>'.$user['first_name'].' '.$user['last_name'].' </td>
												<td>'.$user['username'].'</td>
												<td>'.$user['created_at'].'</td>
												<td>'.$user['updated_at'].'</td>
											</tr>
								';
							}

							print '
										</tbody>
									</table>
								</div>
							';

						
						} catch (Longman\TelegramBot\Exception\TelegramException $e) {
							echo $e->getMessage();
							// Log telegram errors
							Longman\TelegramBot\TelegramLog::error($e);
						} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
							// Catch log initialisation errors
							echo $e->getMessage();
						}
					?>

					
					
				</div>
			</div>
		</div>

<?php require_once __DIR__.'/footer.php'; ?>		