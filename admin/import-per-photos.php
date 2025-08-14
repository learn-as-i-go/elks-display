<?php
// Set page-specific variables
$page_title = 'Import PER Photos';
$page_icon = '📥';
$page_description = 'One-time import of standardized PER photo files';

// Include unified header
include 'header.php';

// List of all PER files in order
$per_files = [
    'PER-1894_John-R-Bennett',
    'PER-1894_Henry-A-Wolf',
    'PER-1895_James-C-McLaughlin',
    'PER-1896_Arthur-Jones',
    'PER-1897_Louis-H-Kanitz',
    'PER-1898_Samuel-Rosen',
    'PER-1900_Paul-R-Beardsley',
    'PER-1901_Louis-Lunsford',
    'PER-1902_Walter-D-Rosie',
    'PER-1903_Lincoln-Rodgers',
    'PER-1904_Will-J-Weller',
    'PER-1905_Guy-H-Sibley',
    'PER-1906_Edward-D-Margoon',
    'PER-1907_Franklin-W-Norton',
    'PER-1908_Wm-E-Thorton',
    'PER-1909_Elliot-D-Prescott',
    'PER-1910_Edwin-C-Robinson',
    'PER-1911_Charles-C-Marsh',
    'PER-1912_Roy-E-Alberts',
    'PER-1913_William-T-Evans',
    'PER-1914_Lee-H-Trott',
    'PER-1915_William-J-Smith',
    'PER-1916_J-O-Matterson',
    'PER-1917_Frank-E-Anderson',
    'PER-1918_NormanHolthe',
    'PER-1919_James-M-Donnelly',
    'PER-1920_Arthur-W-Bergeon',
    'PER-1921_James-Albers',
    'PER-1922_William-B-Backstrom',
    'PER-1923_Edward-J-LeTarte',
    'PER-1924_C-Lester-Nelson',
    'PER-1925_John-Olsen',
    'PER-1926_Sophus-A-Lund',
    'PER-1927_Nellis-Steketee',
    'PER-1928_Paul-A-Sifferd',
    'PER-1929_Richard-W-Cone',
    'PER-1930_Karl-R-Kopanka',
    'PER-1931_Clark-W-Buck',
    'PER-1932_George-F-Liddle',
    'PER-1933_Todore-G-Clock',
    'PER-1934_Phillip-Murphy',
    'PER-1935_John-O-Vegter',
    'PER-1936_Clarence-A-Ahnstrom',
    'PER-1937_Wayne-R-Hilt',
    'PER-1938_Harry-E-Lowes',
    'PER-1939_J-Ernest-Hoos',
    'PER-1940_Arthur-J-Siplon',
    'PER-1941_Harry-J-Bitzer',
    'PER-1942_R-S-Jorgensen',
    'PER-1943_Thaddeus-Jones',
    'PER-1944_Charles-Sutton',
    'PER-1945_George-Sorenson',
    'PER-1946_Henery-Appelt',
    'PER-1947_Roy-Winters',
    'PER-1948_Edward-J-Allard',
    'PER-1949_M-J-Kennebeck',
    'PER-1950_Eugene-Brown',
    'PER-1951_Arthur-Stordahl',
    'PER-1952_Thurlow-E-King',
    'PER-1953_Carl-Anderson',
    'PER-1954_Hubert-F-Dodge',
    'PER-1955_Louis-Hodges',
    'PER-1956_JohnB-Olsen',
    'PER-1957_Roy-Smrcina',
    'PER-1958_Jack-W-Wiersma',
    'PER-1959_Comet-R-Halley',
    'PER-1960_Alfred-Anderson',
    'PER-1961_J-DMcMillan',
    'PER-1962_Orville-Souser',
    'PER-1963_Melvin-J-DeYoung',
    'PER-1964_Albert-N-Falony',
    'PER-1965_Charles-E-Briggs',
    'PER-1966_Theodore-A-Elwell',
    'PER-1967_Dave-Kendall',
    'PER-1968_George-E-Schaefer',
    'PER-1969_William-Miller',
    'PER-1970_Donald-VanBemmelen',
    'PER-1971_Raymond-Morency-Jr',
    'PER-1972_Jared-E-Collinge',
    'PER-1973_Edward-C-Duliban',
    'PER-1974_Glenn-Leatherman',
    'PER-1975_Henry-Brezinski',
    'PER-1976_Fred-Holland',
    'PER-1977_Jerry-L-Wiersma',
    'PER-1978_Joseph-Buckingham',
    'PER-1979_Roger-King',
    'PER-1980_Charls-Rasmussen',
    'PER-1981_Richard-Benton',
    'PER-1982_AlanL-Bohland',
    'PER-1983_Rchard-Benton',
    'PER-1984_Gary-Ackerman',
    'PER-1985_James-S-Smith',
    'PER-1986_Donald-Bedwell',
    'PER-1987_BrianMeier',
    'PER-1988_Rnld-Bohland',
    'PER-1989_Thomas-J-Pastoor',
    'PER-1990_Tomas-C-Chadwick',
    'PER-1991_John-Yarranton',
    'PER-1992_David-Cashbaugh-Sr',
    'PER-1994_Jerry-Wiersma',
    'PER-1996_Alan-L-Bohland',
    'PER-1997_David-R-Cashbaugh-Jr',
    'PER-1998_Randall-Jackson',
    'PER-1999_Gayle-Mullr',
    'PER-2000_Gayle-Muller',
    'PER-2001_Randall-Jackson',
    'PER-2002_William-Vandervelde',
    'PER-2003_David-R-Cashbaugh-Jr',
    'PER-2004_Shaun-Wade',
    'PER-2005_David-R-Cashbaugh-Jr',
    'PER-2006_Randall-Jackson',
    'PER-2007_David-R-Cashbaugh-Jr',
    'PER-2008_JoAnn-Moorehead',
    'PER-2008_Russell-Fairfield',
    'PER-2009_JoAnn-Moorehead',
    'PER-2010_Ricki-Chowning',
    'PER-2011_JoAnn-Moorehead',
    'PER-2012_Rick-Leist',
    'PER-2013_Robert-Spelde'
];

$results = [];
$imported_count = 0;
$skipped_count = 0;
$error_count = 0;

if (isset($_POST['import_photos'])) {
    $results[] = "🚀 Starting PER photo import...";
    $results[] = "📁 Checking directory: uploads/presidents/";
    
    $upload_dir = __DIR__ . '/../uploads/presidents/';
    
    if (!file_exists($upload_dir)) {
        $results[] = "❌ Upload directory does not exist: $upload_dir";
    } else {
        $results[] = "✅ Upload directory found";
        
        // Clear existing PER entries if requested
        if (isset($_POST['clear_existing'])) {
            try {
                $deleted = executeQuery("DELETE FROM presidents");
                $results[] = "🗑️ Cleared existing PER entries";
            } catch (Exception $e) {
                $results[] = "⚠️ Could not clear existing entries: " . $e->getMessage();
            }
        }
        
        foreach ($per_files as $filename_base) {
            // Try different extensions
            $extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $found_file = null;
            $found_extension = null;
            
            foreach ($extensions as $ext) {
                $test_file = $upload_dir . $filename_base . '.' . $ext;
                if (file_exists($test_file)) {
                    $found_file = $test_file;
                    $found_extension = $ext;
                    break;
                }
            }
            
            if (!$found_file) {
                $results[] = "❌ File not found: $filename_base (tried: " . implode(', ', $extensions) . ")";
                $error_count++;
                continue;
            }
            
            // Parse filename to extract year and name
            if (preg_match('/PER-(\d{4})_(.+)/', $filename_base, $matches)) {
                $year = intval($matches[1]);
                $name_part = $matches[2];
                
                // Convert name from "First-Last-Name" to "First Last Name"
                $name = str_replace('-', ' ', $name_part);
                
                // Clean up common abbreviations and formatting
                $name = str_replace(['Wm ', 'Henery'], ['William ', 'Henry'], $name);
                
                $relative_path = 'uploads/presidents/' . $filename_base . '.' . $found_extension;
                
                try {
                    // Check if this entry already exists
                    $existing = fetchOne("SELECT id FROM presidents WHERE year = ? AND name = ?", [$year, $name]);
                    
                    if ($existing && !isset($_POST['clear_existing'])) {
                        $results[] = "⏭️ Skipped (exists): $name ($year)";
                        $skipped_count++;
                    } else {
                        // Insert new record
                        executeQuery(
                            "INSERT INTO presidents (name, year, image_path) VALUES (?, ?, ?)",
                            [$name, $year, $relative_path]
                        );
                        
                        $results[] = "✅ Imported: $name ($year) - $filename_base.$found_extension";
                        $imported_count++;
                    }
                    
                } catch (Exception $e) {
                    $results[] = "❌ Database error for $filename_base: " . $e->getMessage();
                    $error_count++;
                }
                
            } else {
                $results[] = "❌ Could not parse filename: $filename_base";
                $error_count++;
            }
        }
        
        $results[] = "";
        $results[] = "📊 Import Summary:";
        $results[] = "✅ Imported: $imported_count";
        $results[] = "⏭️ Skipped: $skipped_count";
        $results[] = "❌ Errors: $error_count";
        $results[] = "📁 Total files processed: " . count($per_files);
        
        if ($imported_count > 0) {
            $_SESSION['message'] = "Successfully imported $imported_count PER photos!";
        }
    }
}

// Check current status
$current_count = 0;
try {
    $current_count = fetchCount("SELECT COUNT(*) FROM presidents");
} catch (Exception $e) {
    // Ignore error for display
}
?>

<div class="help-text">
    <h3>📥 PER Photo Import</h3>
    <p>This one-time script will import all your standardized PER photos from the uploads/presidents/ directory.</p>
    <ul style="margin-left: 20px;">
        <li><strong>Expected format:</strong> PER-[YEAR]_[First-Last-Name].jpg</li>
        <li><strong>Total files:</strong> <?= count($per_files) ?> PER photos from 1894-2013</li>
        <li><strong>Current database:</strong> <?= $current_count ?> entries</li>
    </ul>
</div>

<div class="form-section">
    <h2>🚀 Import PER Photos</h2>
    
    <?php if (empty($results)): ?>
        <form method="POST">
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="clear_existing" name="clear_existing">
                    <label for="clear_existing">Clear existing PER entries before import</label>
                </div>
                <small style="color: #6c757d;">Check this if you want to start fresh and replace all existing entries.</small>
            </div>
            
            <button type="submit" name="import_photos" class="btn btn-primary">📥 Import All PER Photos</button>
            
            <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px;">
                <strong>⚠️ Important:</strong> Make sure all PER photo files are uploaded to <code>uploads/presidents/</code> directory before running this import.
            </div>
        </form>
    <?php else: ?>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 14px; max-height: 500px; overflow-y: auto;">
            <h3>📋 Import Results</h3>
            <pre style="white-space: pre-wrap;"><?php
                foreach ($results as $result) {
                    echo htmlspecialchars($result) . "\n";
                }
            ?></pre>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="exalted-rulers.php" class="btn btn-success">👑 View Exalted Rulers</a>
            <a href="../display/" target="_blank" class="btn btn-primary">📺 View Display</a>
            <a href="import-per-photos.php" class="btn btn-secondary">🔄 Run Again</a>
        </div>
    <?php endif; ?>
</div>

<div class="form-section">
    <h2>📋 Files to Import</h2>
    <div style="background: white; padding: 20px; border-radius: 8px; max-height: 400px; overflow-y: auto;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 10px; font-family: monospace; font-size: 14px;">
            <?php foreach ($per_files as $index => $filename): ?>
                <div style="padding: 5px; border-bottom: 1px solid #eee;">
                    <?= ($index + 1) ?>. <?= htmlspecialchars($filename) ?>.jpg
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="form-section">
    <h2>🔍 File Check</h2>
    <div style="background: white; padding: 20px; border-radius: 8px;">
        <p>Before importing, verify these files exist in your uploads/presidents/ directory:</p>
        
        <?php
        $upload_dir = __DIR__ . '/../uploads/presidents/';
        $missing_files = [];
        $found_files = [];
        
        foreach (array_slice($per_files, 0, 10) as $filename_base) { // Check first 10 as sample
            $extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $found = false;
            
            foreach ($extensions as $ext) {
                if (file_exists($upload_dir . $filename_base . '.' . $ext)) {
                    $found_files[] = $filename_base . '.' . $ext;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $missing_files[] = $filename_base;
            }
        }
        ?>
        
        <div style="margin-top: 15px;">
            <strong>Sample Check (first 10 files):</strong><br>
            ✅ Found: <?= count($found_files) ?><br>
            ❌ Missing: <?= count($missing_files) ?><br>
            
            <?php if (!empty($missing_files)): ?>
                <div style="color: #dc3545; margin-top: 10px;">
                    <strong>Missing files:</strong><br>
                    <?php foreach ($missing_files as $missing): ?>
                        • <?= htmlspecialchars($missing) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
