/*Ransom Note
Given 2 strings, write a function that returns true if the entirety of the character set of String A could comprise the character set of String B.  Real world explanation is that you have a magazine and a note; you want to cut out letters from the magazine to create the note.  Does the Magazine have enough of each letter to create the note? (Needs the correct letters as well as the correct number of each, based on what the Note contains.)
*/


function containsAllLetters(source, target) {
    if (target.length > source.length) {
        return false;
    }
    let targetMap = {};
    for (let i=0; i<target.length; i++) {
        if (targetMap[target[i]] !== undefined) {
            targetMap[target[i]] +=  1;
        }
        else {
            targetMap[target[i]] = 1;
        }
     }

     for (let j=0; j<source.length; j++) {
         let targeted = targetMap[source[j]];
         if (targeted !== undefined) {
             if (targeted > 1) {
                 targetMap[source[j]]--;
             }
             else {
                 delete targetMap[source[j]];
             }
         }
     }
     if (Object.keys(targetMap).length > 0) {
         return false;
     }
     return true;
}
