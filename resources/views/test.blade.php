<!DOCTYPE html>
<html>
<head>
    <title>Jasmine Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jasmine/3.6.0/jasmine.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jasmine/3.6.0/jasmine.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jasmine/3.6.0/jasmine-html.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jasmine/3.6.0/boot.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>
</head>
<body>
    <h1>Jasmine Test</h1>

    <script>
        function getOrdinalDate(d) {
            if (d > 3 && d < 21) return d + 'th';
            switch (d % 10) {
                case 1: return d + "st";
                case 2: return d + "nd";
                case 3: return d + "rd";
                default: return d + "th";
            }
        }

        function getNextMondayDate() {
            var now = moment().tz("Europe/Dublin"); // Current date and time in Dublin time

            var today = now.day(); // Get the current day of the week (0-6, 0=Sunday)
            var hours = now.hour();
            var minutes = now.minute();

            // Check if today is Sunday
            if (today === 0) {
                // Next Monday is tomorrow
                var nextMonday = now.clone().day(1);
                var nextMondayDate = getOrdinalDate(nextMonday.date()) + ' ' + nextMonday.format('MMMM YYYY');
                return nextMondayDate;
            }

            // Check if today is Tuesday and before 11:59 AM
            if (today === 2 && (hours < 12 || (hours === 12 && minutes === 0))) {
                var thisMonday = now.clone().day(1); // This week's Monday
                var thisMondayDate = getOrdinalDate(thisMonday.date()) + ' ' + thisMonday.format('MMMM YYYY');
                return thisMondayDate;
            }

            // If today is Tuesday and after 12:00 PM
            if (today === 2 && (hours > 12 || (hours === 12 && minutes > 0))) {
                var nextMonday = now.clone().day(8); // Next Monday
                var nextMondayDate = getOrdinalDate(nextMonday.date()) + ' ' + nextMonday.format('MMMM YYYY');
                return nextMondayDate;
            }

            // For all other days, calculate the next Monday
            var mondayEnd = now.clone().day(1).startOf('day').add(35, 'hours'); // Start of Monday plus 35 hours

            if (now.isAfter(mondayEnd)) {
                var nextMonday = now.clone().day(8); // Move to the next Monday
                var nextMondayDate = getOrdinalDate(nextMonday.date()) + ' ' + nextMonday.format('MMMM YYYY');
                return nextMondayDate;
            } else {
                var thisMonday = now.clone().day(1); // This week's Monday
                var thisMondayDate = getOrdinalDate(thisMonday.date()) + ' ' + thisMonday.format('MMMM YYYY');
                return thisMondayDate;
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            var dateText = getNextMondayDate();
            var heading = document.getElementById("nextMondayDate");
            if (heading) {
                heading.innerHTML = 'Courses Starting Next Monday ' + dateText;
            }
        });
    </script>

    <script>
        describe("getNextMondayDate", function() {
            it("should return 1st July 2024 when today is Sunday, 30th June 2024", function() {
                spyOn(moment.tz, 'guess').and.returnValue('Europe/Dublin');
                jasmine.clock().install();
                jasmine.clock().mockDate(new Date('2024-06-30T00:00:00Z'));

                var result = getNextMondayDate();

                expect(result).toBe("1st July 2024");

                jasmine.clock().uninstall();
            });
            
            it("should return 8th July 2024 when today is Wednesday, 3rd July 2024", function() {
                spyOn(moment.tz, 'guess').and.returnValue('Europe/Dublin');
                jasmine.clock().install();
                jasmine.clock().mockDate(new Date('2024-07-03T00:00:00Z'));

                var result = getNextMondayDate();

                expect(result).toBe("8th July 2024");

                jasmine.clock().uninstall();
            });

            it("should return 8th July 2024 when today is Sunday, 7th July 2024", function() {
                spyOn(moment.tz, 'guess').and.returnValue('Europe/Dublin');
                jasmine.clock().install();
                jasmine.clock().mockDate(new Date('2024-07-07T00:00:00Z'));

                var result = getNextMondayDate();

                expect(result).toBe("8th July 2024");

                jasmine.clock().uninstall();
            });

            it("should return 8th July 2024 when today is Tuesday, 9th July 2024 11am", function() {
                spyOn(moment.tz, 'guess').and.returnValue('Europe/Dublin');
                jasmine.clock().install();
                jasmine.clock().mockDate(new Date('2024-07-09T00:11:00Z'));

                var result = getNextMondayDate();

                expect(result).toBe("8th July 2024");

                jasmine.clock().uninstall();
            });
        });
    </script>
</body>
</html>
