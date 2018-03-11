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
						require_once __DIR__.'/../ForecastDB.php';
						require_once __DIR__.'/../SubscriptionDB.php';
						require_once __DIR__.'/../SubscriberDB.php';

						use Longman\TelegramBot\DB;
						
						CapperDB::initializeCapper();
						UserDB::initializeUser();
						ForecastDB::initializeForecast();
						SubscriptionDB::initializeSubscription();
						SubscriberDB::initializeSubscriber();

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

							if(isset($_GET['remove_capper_id'])) {
								CapperDB::deleteCapper($_GET['remove_capper_id']);
							}

							print '<div class="row placeholders">';

							$cappers = CapperDB::selectCapper();

							foreach ($cappers as $capper) {
								print '
									<div class="col-xs-6 col-sm-3 placeholder">
										<a href="capper-edit.php?id='.$capper['id'].'">
											<img src="'.$images_dir.current(glob($images_dir.$capper['id'].'.*')).'" class="img-responsive" alt="Generic placeholder thumbnail">
										</a>
										<h4>'.$capper['name'].'</h4>
										<span class="text-muted">'.$capper['description'].'</span><br>
										<span>
											<a href="capper-edit.php?id='.$capper['id'].'">Изменить</a> 
											<a href="index.php?remove_capper_id='.$capper['id'].'">Удалить</a> 
											<a href="forecast.php?capper_id='.$capper['id'].'">Прогнозы ('.count(ForecastDB::selectForecast(null, $capper['id'])).')</a>
										</span>
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
												<td>
													
													<button class="btn btn-default" data-toggle="collapse" data-target="#hide-subscriber-'.$user['id'].'">'.count(SubscriberDB::selectSubscriber(null, null, null, $user['id'], null, null, null, 1)).' шт, показать</button>
													<div id="hide-subscriber-'.$user['id'].'" class="collapse">
														Нет подписок
													</div>
												</td>
											</tr>
								';
							}

							print '
										</tbody>
									</table>
								</div>
							';

							print '<h2 class="sub-header">Типы подписок</h2>
								<div class="table-responsive">
									<table class="table table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>Название</th>
												<th>Длителньость</th>
												<th>Цена</th>
											</tr>
										</thead>
										<tbody>
							';


							$subscriptions = SubscriptionDB::selectSubscription();

							foreach ($subscriptions as $subscription) {

								print '
											<tr>
												<td>'.$subscription['id'].'</td>
												<td>'.$subscription['name'].' </td>
												<td>'.($subscription['duration'] / 60 / 60 / 24).' д. </td>
												<td>'.$subscription['price'].' руб.</td>
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