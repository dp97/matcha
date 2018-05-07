<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Search</title>
        <link rel="stylesheet" href="https://unpkg.com/purecss@1.0.0/build/pure-min.css" integrity="sha384-nn4HPE8lTHyVtfCBi5yW9d20FjT8BJwUXyWZT9InLYax14RDjBj46LmSztkmNP9w" crossorigin="anonymous">
        <link rel="stylesheet" href="templates/css/stylesheet.css" >
    </head>
    <body>
        <!--include header content-->
        <?PHP include 'inc/header.php';?>
        
        <h1>Search For Perfect Match</h1>
        
        <div style="margin: 10px;">
            <div>
                <label for="ageFrom">Age From</label>
                <select id="ageFrom">
                    <?PHP
                        for ($i = 1; $i < 100; $i++) {
                            echo "<option>$i</option>";
                        }
                    ?>
                </select>
                <label for="ageTo">To</label>
                <select id="ageTo">
                    <?PHP
                        for ($i = 1; $i < 100; $i++) {
                            echo "<option ". (($i == 99) ? 'selected' : null) .">$i</option>";
                        }
                    ?>
                </select>
            </div>
            
            <div>
                <label for="ratingFrom">Fame From</label>
                <select id="ratingFrom">
                    <?PHP
                        for ($i = 1; $i <= 10; $i++) {
                            echo "<option>$i</option>";
                        }
                    ?>
                </select>
                <label for="ratingTo">To</label>
                <select id="ratingTo">
                    <?PHP
                        for ($i = 1; $i <= 10; $i++) {
                            echo "<option ". (($i == 10) ? 'selected' : null) .">$i</option>";
                        }
                    ?>
                </select>
            </div>
            <div>
                <label for="sortedBy">Sorted By:</label>
                <select id="sortedBy" onchange="sortProfiles()">
                    <option selected value="age">age</option>
                    <option value="tags">tags</option>
                    <option value="rating">fame rating</option>
                </select>
            </div>
            
            <div class="tag-field">
                <?PHP
                    foreach ($atags as $tag) {
                        $name = $tag['name'];
                        echo "<div id='tag' onclick='selectTag(this, \"$name\")' class='normal-tag'>$name</div>";
                    }
                ?>
            </div>
            
            <button id="filter-button" onclick="filter()" class='pure-button pure-button-primary' style="box-sizing: border-box; width: 100%;">Filter</button>
        </div>
            
        <div class="pure-g search-result" id="result-set">
            <div class="pure-u-1" id="message"><p>Looks like no results found!</p></div>
        </div>
        
        <script>
            var selectedTags = [];
            var allData = {};
            
            function sortProfiles() {
                var parent = document.getElementById('result-set');
                var sortBy = document.getElementById('sortedBy').value;
                
                parent.children.sort(function (a, b) {
                    var age1 = a.chlidren[1].innerHTML;
                    var age2 = b.children[1].innerHTML;
                    return age1 - age2;
                });
            }
            
            function selectTag(tag, tagName) {
                if (tag.className == "normal-tag") {
                    selectedTags.push(tagName);
                    tag.className = "selected-tag";
                } else {
                    for (i in selectedTags) {
                        if (selectedTags[i] == tagName) {
                            selectedTags.splice(i, 1);
                            break;
                        }
                    }
                    tag.className = "normal-tag";
                }
            }
        
            function filter() {
                var data = {
                    "age": {
                        "from": parseInt(document.getElementById('ageFrom').value, 10),
                        "to": parseInt(document.getElementById('ageTo').value, 10)
                        },
                    "rating": {
                        "from": parseInt(document.getElementById('ratingFrom').value, 10),
                        "to": parseInt(document.getElementById('ratingTo').value, 10)
                        },
                    "filterTags": selectedTags,
                    "sortedBy": document.getElementById('sortedBy').value
                };
                undisplayResults();
                
                var filteredData = JSON.parse(JSON.stringify(allData));
                var i = 0;
                while (i < filteredData.length) {
                    if (filteredData[i].age < data.age.from || filteredData[i].age > data.age.to
                        || filteredData[i].rating < data.rating.from || filteredData[i].rating > data.rating.to) {
                        filteredData.splice(i, 1);
                        continue;
                    }
                    // if (data.filterTags.length != 0) {
                        
                    // }
                    i++;
                }
                
                filteredData.sort( function (a, b) {
                    return a.age - b.age;
                });
                
                displayResults(filteredData);
            }
            
            function undisplayResults() {
                document.getElementById('result-set').innerHTML = "";
            }
        
            function visitUser(id) {
                window.location.replace('/user/' + id);
            }
            
            window.onload = function () {
                var ajax = new XMLHttpRequest();
                
                ajax.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        allData = JSON.parse(this.responseText);
                        displayResults(allData);
                    }
                };
                ajax.open("GET", "/api/preview/users", true);
                ajax.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                ajax.send();
            }
            
            function displayResults(data) {
                if (data.length == 0) {
                    console.log('>'+data);
                    document.getElementById('message').style.visibility = "visible";
                    return ;
                }
                console.log('->'+data);
                
                var div, div2, div3, div4, divs, name, img, p, i = 0;
                // for (var j = 0; j < 5;j++){
                for (i in data) {
                    div = document.createElement("div");
                    div.className = "pure-u-1-5 profile-container";
                    img = document.createElement("img");
                    img.className = "pure-img";
                    img.src = data[i].photo;
                    
                    div2 = document.createElement("div");
                    div2.className = "pure-u-1";
                    name = document.createElement("h2");
                    name.innerHTML = data[i].uname;
                    name.style.color = "#29323c";
                    name.style.textAlign = "center";
                    div2.appendChild(name);
                    
                    div3 = document.createElement('div');
                    div3.style.background = "blanchedalmond";
                    p = document.createElement("p");
                    p.style.margin = "0";
                    p.style.textAlign = "center";
                    p.innerHTML = "Age: <span style='font-weight: bold;'>" + data[i].age + "</span> fame: <span style='font-weight: bold;'>" + data[i].rating + "</span>/10";
                    div3.appendChild(p);
                    
                    div4 = document.createElement('div');
                    div4.className = "tag-field";
                    var j = 0;
                    for (j in data[i]['tags']) {
                        divs = document.createElement('div');
                        divs.id = "tag";
                        divs.className = "normal-tag";
                        divs.innerHTML = data[i]['tags'][j];
                        div4.appendChild(divs);
                    }
                    
                    div.appendChild(img);
                    div.appendChild(div2);
                    div.appendChild(div3);
                    div.appendChild(div4);
                    div.onclick = ( function (id) {
                        return function(){
                            visitUser(id);
                        };
                    })(data[i].id);
                    document.getElementById('result-set').appendChild(div);
                }
                // }
            }
        </script>
        
        <!--inlcude footer content-->
        <?PHP include 'inc/footer.php';?>
    </body>
</html>