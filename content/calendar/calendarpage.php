<?php
$events = getEventsArray();
$closestEvent = getSpecificEventById(getClosestEventId(), false);
?>
<div class='row'>
    <div class='row bg-lightdark mb-3 gx-0'>
        <div class='col bg-blackened'>
            <?php include(__DIR__ . "/calendar.php"); ?>
        </div>
        <div id='eventDisplaySwitchable' class='col bg-lightdark rounded'>
            <!-- Inhalt wird durch klick auf ein Event ausgetauscht (default-wert: nächstes anstehendes Event) -->
            <?php
            foreach ($closestEvent as $row) {
                echo "<div class='row text-center bg-blackened m-1 p-1 rounded'>";
                printf("<h4><u>Upcoming</u>: %s</h4>", $row["title"]);
                echo "</div><div class='row'><div class='col d-flex justify-content-center align-items-center'>";
                getCategoryImage($row['event_cat'], 128, 1);
                echo "</div>
                    <div class='col flex-grow-1'><hr/>
                        <div class='row p-2'>";
                printf("Beginn: %s", $row["start"]);
                echo "</div><div class='row p-2'>";
                printf("%s", $row["description"]);
                echo "      </div>
                            </div>
                        </div>";
            }
            ?>
        </div>
    </div>
    <hr/>
    <div class='col'>
        <!-- iterate through next 3-4 upcoming events and display them, each in a div-row -->
        <?php
        foreach ($events as $row) {
            echo "<div class ='row calendar-event mb-2 p-2 bg-lightdark rounded'>
                        <div class='row text-center bg-blackened m-1 p-1 rounded'>";
            printf("<h4>%s</h4>", $row["title"]);
            echo "</div><div class='row'><div class='col'>";
            printf("%s", getCategoryImage($row['event_cat'], 128, 1));
            echo "</div>
                <div class='col flex-grow-1'><hr/>
                    <div class='row p-2'>";
            printf("Beginn: %s", $row["start"]);
            echo "</div><div class='row p-2'>";
            printf("%s", $row["description"]);
            echo "      </div>
                        </div>
                    </div>
                </div>";
        }
        ?>
    </div>
</div>