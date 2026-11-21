param([string]$ArchiveRoot = 'D:\Project\portal-berita\storage\app\private\imports\kapuasraya')
$ErrorActionPreference = 'Stop'
$site = 'https://kapuasraya.com'
New-Item -ItemType Directory -Force -Path $ArchiveRoot | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $ArchiveRoot 'images') | Out-Null
$manifest = [ordered]@{source=$site;captured_at=[DateTime]::UtcNow.ToString('o');counts=@{};files=@();errors=@()}
foreach ($endpoint in @('posts','pages','categories','tags','media')) {
    $all = [System.Collections.Generic.List[object]]::new()
    $page = 1; $expected = 0
    try {
        do {
            $response = Invoke-WebRequest "$site/wp-json/wp/v2/${endpoint}?per_page=20&page=$page" -UseBasicParsing -TimeoutSec 45
            $items = $response.Content | ConvertFrom-Json
            if (-not $items) { break }
            $all.AddRange([object[]]$items)
            $headerTotal = $response.Headers['X-WP-Total'] | Select-Object -First 1
            if ($headerTotal) { $expected = [int]$headerTotal }
            $page++
        } while ($all.Count -lt $expected -and $page -le [Math]::Ceiling($expected / 20.0))
        ConvertTo-Json -InputObject $all.ToArray() -Depth 60 | Set-Content -LiteralPath (Join-Path $ArchiveRoot "$endpoint.json") -Encoding utf8
        $manifest.counts[$endpoint] = @{expected=$expected;captured=$all.Count}
        Write-Output "$endpoint : $($all.Count)/$expected"
    } catch { $manifest.errors += @{resource=$endpoint;error=$_.Exception.Message} }
}
foreach($resource in @(@{url="$site/";file='homepage.html'},@{url="$site/wp-json/";file='site.json'},@{url="$site/robots.txt";file='robots.txt'},@{url="$site/wp-sitemap.xml";file='sitemap.xml'})) {
    try { (Invoke-WebRequest $resource.url -UseBasicParsing -TimeoutSec 40).Content | Set-Content -LiteralPath (Join-Path $ArchiveRoot $resource.file) -Encoding utf8 } catch {$manifest.errors += @{resource=$resource.file;error=$_.Exception.Message}}
}
$urls = [System.Collections.Generic.HashSet[string]]::new()
$mediaPath=Join-Path $ArchiveRoot 'media.json'
if(Test-Path $mediaPath) { foreach($media in (Get-Content $mediaPath -Raw | ConvertFrom-Json)) { if($media.source_url) {[void]$urls.Add($media.source_url)} } }
foreach($endpoint in @('posts','pages')) {
    $jsonPath=Join-Path $ArchiveRoot "$endpoint.json"
    if(Test-Path $jsonPath) { foreach($entry in (Get-Content $jsonPath -Raw | ConvertFrom-Json)) {
        foreach($match in [regex]::Matches($entry.content.rendered,'(?i)<img[^>]+src=["'']([^"'']+)')) { [void]$urls.Add([System.Net.WebUtility]::HtmlDecode($match.Groups[1].Value)) }
    } }
}
$index = @(); $number=0
foreach($url in $urls) {
    try {
        $uri=[Uri]$url
        if($uri.Scheme -ne 'https' -or $uri.Host -notin @('kapuasraya.com','www.kapuasraya.com','img-s-msn-com.akamaized.net')) { throw 'Sumber di luar domain dilewati.' }
        $sha=[System.Security.Cryptography.SHA256]::Create()
        $hashBytes = $sha.ComputeHash([Text.Encoding]::UTF8.GetBytes($url))
        $hash = ([System.BitConverter]::ToString($hashBytes) -replace '-','').ToLowerInvariant()
        $sha.Dispose()
        $relative="images/$hash.bin"; $path=Join-Path $ArchiveRoot $relative
        if(!(Test-Path $path)) { Invoke-WebRequest $url -OutFile $path -TimeoutSec 60 -MaximumRedirection 0 }
        $index += @{url=$url;file=$relative;bytes=(Get-Item -LiteralPath $path).Length;sha256=(Get-FileHash -LiteralPath $path -Algorithm SHA256).Hash.ToLowerInvariant()}
    } catch {$manifest.errors += @{resource=$url;error=$_.Exception.Message}}
    $number++; if($number % 20 -eq 0) {Write-Output "Images: $number/$($urls.Count)"}
}
ConvertTo-Json -InputObject @($index) -Depth 10 | Set-Content -LiteralPath (Join-Path $ArchiveRoot 'image-index.json') -Encoding utf8
$manifest.counts['images']=@{expected=$urls.Count;captured=$index.Count}
$manifest.files=@(Get-ChildItem -LiteralPath $ArchiveRoot -File | Select-Object -ExpandProperty Name)
$manifest | ConvertTo-Json -Depth 20 | Set-Content -LiteralPath (Join-Path $ArchiveRoot 'manifest.json') -Encoding utf8
Write-Output "Archive complete: $ArchiveRoot; images=$($index.Count); errors=$($manifest.errors.Count)"
