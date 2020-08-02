<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Bienvenue sur dFramework 3</title>
	<meta name="description" content="The simplest PHP framework for beginners">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" type="image/png" href="/favicon.ico"/>

	<!-- STYLES -->

	<style {csp-style-nonce}>
		* {
			transition: background-color 300ms ease, color 300ms ease;
		}
		*:focus {
			background-color: rgba(221, 72, 20, .2);
			outline: none;
		}
		html, body {
			color: rgba(33, 37, 41, 1);
			font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
			font-size: 16px;
			margin: 0;
			padding: 0;
			-webkit-font-smoothing: antialiased;
			-moz-osx-font-smoothing: grayscale;
			text-rendering: optimizeLegibility;
		}
		header {
			background-color: rgba(247, 248, 249, 1);
			padding: .4rem 0 0;
		}
		.menu {
			padding: .4rem 2rem;
		}
		header ul {
			border-bottom: 1px solid rgba(242, 242, 242, 1);
			list-style-type: none;
			margin: 0;
			overflow: hidden;
			padding: 0;
			text-align: right;
		}
		header li {
			display: inline-block;
		}
		header li a {
			border-radius: 5px;
			color: rgba(0, 0, 0, .5);
			display: block;
			height: 44px;
			text-decoration: none;
		}
		header li.menu-item a {
			border-radius: 5px;
			margin: 5px 0;
			height: 38px;
			line-height: 36px;
			padding: .4rem .65rem;
			text-align: center;
		}
		header li.menu-item a:hover,
		header li.menu-item a:focus {
			background-color: rgba(221, 72, 20, .2);
			color: rgba(221, 72, 20, 1);
		}
		header .logo {
			float: left;
			height: 44px;
			padding: .4rem .5rem;
		}
		header .menu-toggle {
			display: none;
			float: right;
			font-size: 2rem;
			font-weight: bold;
		}
		header .menu-toggle button {
			background-color: rgba(221, 72, 20, .6);
			border: none;
			border-radius: 3px;
			color: rgba(255, 255, 255, 1);
			cursor: pointer;
			font: inherit;
			font-size: 1.3rem;
			height: 36px;
			padding: 0;
			margin: 11px 0;
			overflow: visible;
			width: 40px;
		}
		header .menu-toggle button:hover,
		header .menu-toggle button:focus {
			background-color: rgba(221, 72, 20, .8);
			color: rgba(255, 255, 255, .8);
		}
		header .heroe {
			margin: 0 auto;
			max-width: 1100px;
			padding: 1rem 1.75rem 1.75rem 1.75rem;
		}
		header .heroe h1 {
			font-size: 2.5rem;
			font-weight: 500;
		}
		header .heroe h2 {
			font-size: 1.5rem;
			font-weight: 300;
		}
		section {
			margin: 0 auto;
			padding: 2.5rem 1.75rem 3.5rem 1.75rem;
        }
		section h1 {
			margin-bottom: 2.5rem;
		}
		section h2 {
			font-size: 120%;
			line-height: 2.5rem;
			padding-top: 1.5rem;
		}
		section pre {
			background-color: rgba(247, 248, 249, 1);
			border: 1px solid rgba(242, 242, 242, 1);
			display: block;
			font-size: .9rem;
			margin: 2rem 0;
			padding: 1rem 1.5rem;
			white-space: pre-wrap;
			word-break: break-all;
		}
		section code {
			display: block;
		}
		section a {
			color: rgba(221, 72, 20, 1);
		}
		section svg {
			margin-bottom: -5px;
			margin-right: 5px;
			width: 25px;
		}
		.further {
			background-color: rgba(247, 248, 249, 1);
			border-bottom: 1px solid rgba(242, 242, 242, 1);
			border-top: 1px solid rgba(242, 242, 242, 1);
		}
		.further h2:first-of-type {
			padding-top: 0;
		}
		footer {
			background-color: rgba(221, 72, 20, .8);
			text-align: center;
		}
		footer .copyrights {
			background-color: rgba(62, 62, 62, 1);
			color: rgba(200, 200, 200, 1);
			padding: .25rem 1.75rem;
		}

        #content {
            display: flex;
            justify-content: space-between;
        }
        #content > div {
            flex: 1;
            margin: 0.5em 2em;
        }
        #content > div img {
            margin-bottom: 1em;
            width: 100%;
			height: 25%;
        }
		@media (max-width: 559px) {
			#content {
				flex-direction: column;
			}
			header ul {
				padding: 0;
			}
			header .menu-toggle {
				padding: 0 1rem;
			}
			header .menu-item {
				background-color: rgba(244, 245, 246, 1);
				border-top: 1px solid rgba(242, 242, 242, 1);
				margin: 0 15px;
				width: calc(100% - 30px);
			}
			header .menu-toggle {
				display: block;
			}
			header .hidden {
				display: none;
			}
			header li.menu-item a {
				background-color: rgba(221, 72, 20, .1);
			}
			header li.menu-item a:hover,
			header li.menu-item a:focus {
				background-color: rgba(221, 72, 20, .7);
				color: rgba(255, 255, 255, .8);
			}
		}
	</style>
</head>
<body>

<!-- HEADER: MENU + HEROE SECTION -->
<header>

	<div class="menu">
		<ul>
			<li class="logo"><a href="http://dframework.dimtrov.com" target="_blank">
                <img height="44" title="Visiter le site web officiel de dFramework!" alt="Logo dFramework"
					src="data:image/png;base64,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"
                >
            </a></li>
			<li class="menu-toggle">
				<button onclick="toggleMenu();">&#9776;</button>
			</li>
			<li class="menu-item hidden"><a href="#">Acceuil</a></li>
			<li class="menu-item hidden"><a href="http://dframework.dimtrov.com/docs/guide/" target="_blank">Docs</a>
			</li>
			<li class="menu-item hidden"><a href="http://dframework.dimtrov.com/forum" target="_blank">Communauté</a></li>
			<li class="menu-item hidden"><a href="https://github.com/Dimtrov/dframework/blob/master/CONTRIBUTING.md" target="_blank">Contribute</a>
			</li>
		</ul>
	</div>

	<div class="heroe">

		<h1>Bienvenue sur dFramework <?= \dFramework\core\dFramework::VERSION ?></h1>

		<h2>The simplest PHP framework for beginners</h2>

	</div>

</header>

<!-- CONTENT -->

<section id="content">
    <div>
        <img src="data:image/png;base64,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" alt=""/>
    
        <h1>A propos de cette page</h1>

        <p>La page que vous voyez actuellement a été générée dynamiquement par dFramework.</p>

        <p>Si vous voulez éditer cette page, vous la trouverez à l'emplacement</p>

        <pre><code>app/views/welcome.php</code></pre>

        <p>Le contrôleur correspondant à cette page peut être trouvé dans:</p>

        <pre><code>app/controllers/HomeController.php</code></pre>
    </div>
    <div>
        
        <div class="further">   
            <section>
                <h2>
                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><rect x='32' y='96' width='64' height='368' rx='16' ry='16' style='fill:none;stroke:#000;stroke-linejoin:round;stroke-width:32px'/><line x1='112' y1='224' x2='240' y2='224' style='fill:none;stroke:#000;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px'/><line x1='112' y1='400' x2='240' y2='400' style='fill:none;stroke:#000;stroke-linecap:round;stroke-linejoin:round;stroke-width:32px'/><rect x='112' y='160' width='128' height='304' rx='16' ry='16' style='fill:none;stroke:#000;stroke-linejoin:round;stroke-width:32px'/><rect x='256' y='48' width='96' height='416' rx='16' ry='16' style='fill:none;stroke:#000;stroke-linejoin:round;stroke-width:32px'/><path d='M422.46,96.11l-40.4,4.25c-11.12,1.17-19.18,11.57-17.93,23.1l34.92,321.59c1.26,11.53,11.37,20,22.49,18.84l40.4-4.25c11.12-1.17,19.18-11.57,17.93-23.1L445,115C443.69,103.42,433.58,94.94,422.46,96.11Z' style='fill:none;stroke:#000;stroke-linejoin:round;stroke-width:32px'/></svg>
                    Apprendre
                </h2>
                <p>
                    Le Guide de l'utilisateur contient une introduction, un didacticiel, un certain nombre de guides pratiques, 
                    puis une documentation de référence pour les composants qui composent le framework. 
                    Consultez le <a href="http://dframework.dimtrov.com/docs/guide" target="_blank">guide de l' utilisateur</a> !
                </p>
                <h2>
                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M431,320.6c-1-3.6,1.2-8.6,3.3-12.2a33.68,33.68,0,0,1,2.1-3.1A162,162,0,0,0,464,215c.3-92.2-77.5-167-173.7-167C206.4,48,136.4,105.1,120,180.9a160.7,160.7,0,0,0-3.7,34.2c0,92.3,74.8,169.1,171,169.1,15.3,0,35.9-4.6,47.2-7.7s22.5-7.2,25.4-8.3a26.44,26.44,0,0,1,9.3-1.7,26,26,0,0,1,10.1,2L436,388.6a13.52,13.52,0,0,0,3.9,1,8,8,0,0,0,8-8,12.85,12.85,0,0,0-.5-2.7Z' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/><path d='M66.46,232a146.23,146.23,0,0,0,6.39,152.67c2.31,3.49,3.61,6.19,3.21,8s-11.93,61.87-11.93,61.87a8,8,0,0,0,2.71,7.68A8.17,8.17,0,0,0,72,464a7.26,7.26,0,0,0,2.91-.6l56.21-22a15.7,15.7,0,0,1,12,.2c18.94,7.38,39.88,12,60.83,12A159.21,159.21,0,0,0,284,432.11' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/></svg>
                    Discuter
                </h2>
                <p>
                    dFramework est un projet open source ayant plusieurs lieux permettant aux membres de la communauté de se rassembler et d'échanger des idées. 
                    Consultez toutes les discussions sur <a href="http://dframework.dimtrov.com/forum" target="_blank">notre forum</a> !
                </p>
                <h2>
                    <svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><line x1='176' y1='48' x2='336' y2='48' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/><line x1='118' y1='304' x2='394' y2='304' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/><path d='M208,48v93.48a64.09,64.09,0,0,1-9.88,34.18L73.21,373.49C48.4,412.78,76.63,464,123.08,464H388.92c46.45,0,74.68-51.22,49.87-90.51L313.87,175.66A64.09,64.09,0,0,1,304,141.48V48' style='fill:none;stroke:#000;stroke-linecap:round;stroke-miterlimit:10;stroke-width:32px'/></svg>
                    Contribuer
                </h2>
                <p>
                    dFramework est un projet mené par la communauté et accepte les contributions de code et de documentation de la communauté. 
                    Pourquoi ne pas nous rejoindre ?
                </p>
            </section>
        </div>

    </div>
	
</section>


<!-- FOOTER: DEBUG INFO + COPYRIGHTS -->

<footer>
	<div class="copyrights">

		<p>&copy; <?= date('Y') ?> Dimtrov Labs. dFramework est un projet open source distribué sous licence MPL-2.</p>

	</div>

</footer>

<!-- SCRIPTS -->

<script>
	function toggleMenu() {
		var menuItems = document.getElementsByClassName('menu-item');
		for (var i = 0; i < menuItems.length; i++) {
			var menuItem = menuItems[i];
			menuItem.classList.toggle("hidden");
		}
	}
</script>

<!-- -->

</body>
</html>
