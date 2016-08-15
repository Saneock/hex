<?php
function memoryUsage($usage, $base_memory_usage)
{
	printf("Bytes diff: %d\n<br>", $usage - $base_memory_usage);
}

function mem($real = false)
{
	return memory_get_usage($real);
}