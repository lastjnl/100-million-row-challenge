<?php

namespace App;

final class Parser
{
    public function parse(string $inputPath, string $outputPath): void
    {
        $handle = fopen($inputPath, 'r');
        $data = [];
        $json = "";
       
        while ($row = fscanf($handle, "%[^,],%[^\n]\n", $url, $time) !== false) {
            $data[substr($url, 19, strlen($url))][] = substr($time, 0,10);
        }

        fclose($handle);

        foreach ($data as $url => $dates) {
            $countedDates = [];
            foreach ($dates as $date => $count) {

                if (isset($countedDates[$date]) === false) {
                    $countedDates[$date] = 1;
                } else {
                    $countedDates[$date]++;
                }
            }
            $data[$url] = $countedDates;
        }

        file_put_contents('test.json', json_encode($data));

        $outputPath = json_encode($data);
    }
}