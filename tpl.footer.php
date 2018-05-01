
<hr />

<p>Loaded in <em><span id="load-time">...</span> ms</em>.</p>

<details>
	<summary>Requests</summary>
	<pre><? print_r($_requests) ?></pre>
</details>

<script>
window.performance && window.performance.timing && window.addEventListener('load', function(e) {
	setTimeout(function() {
		var ms = performance.timing.loadEventEnd - performance.timing.navigationStart;
		document.querySelector('#load-time').textContent = Math.round(ms);
	});
});
</script>

</body>

</html>
